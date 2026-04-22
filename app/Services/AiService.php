<?php

namespace App\Services;

use App\Models\TenantConfigModel;
use App\Models\TenantApiKeyModel;
use CodeIgniter\HTTP\CURLRequest;

class AiService
{
    protected TenantConfigModel $configModel;
    protected TenantApiKeyModel $apiKeyModel;

    public function __construct()
    {
        $this->configModel = new TenantConfigModel();
        $this->apiKeyModel = new TenantApiKeyModel();
    }

    /**
     * Send messages to the AI provider and get a response.
     * Implements automatic key rotation on failure.
     *
     * @param int $tenantId
     * @param string $systemPrompt
     * @param array $messages Array of ['role' => 'user'|'assistant', 'content' => '...']
     * @return string AI response text
     * @throws \RuntimeException If all keys fail
     */
    public function chat(int $tenantId, string $systemPrompt, array $messages): string
    {
        // 1. Get tenant config
        $config = $this->configModel->where('tenant_id', $tenantId)->first();
        if (!$config) {
            throw new \RuntimeException("Tenant config not found for tenant_id: {$tenantId}");
        }

        $provider = $config['ai_provider'] ?? 'gemini';
        $model = ($provider === 'grok')
            ? ($config['grok_model'] ?? 'grok-beta')
            : ($config['gemini_model'] ?? 'gemini-1.5-flash');

        // 2. Get active keys ordered by priority
        $keys = $this->apiKeyModel
            ->where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', 1)
            ->orderBy('priority', 'ASC')
            ->findAll();

        if (empty($keys)) {
            throw new \RuntimeException("No active API keys found for tenant_id: {$tenantId}, provider: {$provider}");
        }

        // 3. Iterate keys with rotation
        $lastError = null;
        foreach ($keys as $key) {
            try {
                $decryptedKey = $this->apiKeyModel->decryptKey($key['api_key']);

                $response = ($provider === 'grok')
                    ? $this->callGrok($decryptedKey, $model, $systemPrompt, $messages)
                    : $this->callGemini($decryptedKey, $model, $systemPrompt, $messages);

                // Success: update last_used_at
                $this->apiKeyModel->update($key['id'], [
                    'last_used_at' => date('Y-m-d H:i:s'),
                ]);

                return $response;
            } catch (\Exception $e) {
                $lastError = $e;

                // Error: update last_error_at and last_error_msg, continue to next key
                $this->apiKeyModel->update($key['id'], [
                    'last_error_at' => date('Y-m-d H:i:s'),
                    'last_error_msg' => mb_substr($e->getMessage(), 0, 255),
                ]);

                log_message('error', "[AiService] Key #{$key['id']} ({$key['label']}) failed: {$e->getMessage()}");
                continue;
            }
        }

        // All keys failed
        throw new \RuntimeException(
            "All API keys failed for tenant_id: {$tenantId}, provider: {$provider}. Last error: " . ($lastError ? $lastError->getMessage() : 'unknown')
        );
    }

    /**
     * Get available models for a provider.
     *
     * @param string $provider 'gemini' or 'grok'
     * @param int $tenantId
     * @return array Array of model names
     */
    public function getAvailableModels(string $provider, int $tenantId): array
    {
        $cacheKey = "ai_models_{$provider}_{$tenantId}";
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }

        // Get the first active API key for the provider
        $key = $this->apiKeyModel
            ->where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', 1)
            ->orderBy('priority', 'ASC')
            ->first();

        if (!$key) {
            return [];
        }

        try {
            $decryptedKey = $this->apiKeyModel->decryptKey($key['api_key']);
            $models = ($provider === 'grok')
                ? $this->fetchGrokModels($decryptedKey)
                : $this->fetchGeminiModels($decryptedKey);

            if (!empty($models)) {
                // Cache for 24 hours (86400 seconds)
                cache()->save($cacheKey, $models, 86400);
            }

            return $models;
        } catch (\Exception $e) {
            log_message('error', "[AiService] Failed to fetch models for {$provider}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch available models from Gemini API
     */
    private function fetchGeminiModels(string $apiKey): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";

        $client = \Config\Services::curlrequest();
        $response = $client->request('GET', $url, [
            'http_errors' => false,
            'verify' => false,
        ]);

        $body = json_decode($response->getBody(), true);
        if ($response->getStatusCode() !== 200 || !isset($body['models'])) {
            return [];
        }

        $models = [];
        foreach ($body['models'] as $m) {
            // Only include models that support generateContent
            if (isset($m['supportedGenerationMethods']) && in_array('generateContent', $m['supportedGenerationMethods'])) {
                // Return model name without 'models/' prefix
                $models[] = str_replace('models/', '', $m['name']);
            }
        }

        return $models;
    }

    /**
     * Fetch available models from xAI Grok API
     */
    private function fetchGrokModels(string $apiKey): array
    {
        $url = 'https://api.x.ai/v1/models';

        $client = \Config\Services::curlrequest();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
            ],
            'http_errors' => false,
            'verify' => false,
        ]);

        $body = json_decode($response->getBody(), true);
        if ($response->getStatusCode() !== 200 || !isset($body['data'])) {
            return [];
        }

        $models = [];
        foreach ($body['data'] as $m) {
            $models[] = $m['id'];
        }

        return $models;
    }

    /**
     * Call Google Gemini API
     *
     * @param string $apiKey Decrypted API key
     * @param string $model Gemini model name
     * @param string $systemPrompt System instruction
     * @param array $messages Conversation messages
     * @return string Response text
     * @throws \RuntimeException On API error
     */
    private function callGemini(string $apiKey, string $model, string $systemPrompt, array $messages): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Build contents array — convert 'assistant' role to 'model'
        $contents = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
        ];

        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
            'http_errors' => false,
            'verify' => false, // Added verify false here too for local
        ]);

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            $errorMsg = $body['error']['message'] ?? "HTTP {$statusCode}";
            throw new \RuntimeException("Gemini API error: {$errorMsg}");
        }

        // Extract text from response
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($text === null) {
            throw new \RuntimeException("Gemini API returned empty response");
        }

        return $text;
    }

    /**
     * Call xAI Grok API (OpenAI-compatible format)
     *
     * @param string $apiKey Decrypted API key
     * @param string $model Grok model name
     * @param string $systemPrompt System prompt
     * @param array $messages Conversation messages
     * @return string Response text
     * @throws \RuntimeException On API error
     */
    private function callGrok(string $apiKey, string $model, string $systemPrompt, array $messages): string
    {
        $url = 'https://api.x.ai/v1/chat/completions';

        // Build OpenAI-compatible messages array
        $apiMessages = [];

        // System prompt as first message
        $apiMessages[] = [
            'role' => 'system',
            'content' => $systemPrompt,
        ];

        // Append conversation history
        foreach ($messages as $msg) {
            $apiMessages[] = [
                'role' => $msg['role'], // user or assistant
                'content' => $msg['content'],
            ];
        }

        $payload = [
            'model' => $model,
            'messages' => $apiMessages,
        ];

        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$apiKey}",
            ],
            'json' => $payload,
            'http_errors' => false,
            'verify' => false, // Added verify false here too for local
        ]);

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            $errorMsg = $body['error']['message'] ?? "HTTP {$statusCode}";
            throw new \RuntimeException("Grok API error: {$errorMsg}");
        }

        // Extract text from response
        $text = $body['choices'][0]['message']['content'] ?? null;
        if ($text === null) {
            throw new \RuntimeException("Grok API returned empty response");
        }

        return $text;
    }
}
