<?php

// Load CodeIgniter 4 environment
require_once __DIR__ . '/public/index.php';

// Mock TENANT_ID if not defined
if (!defined('TENANT_ID')) {
    define('TENANT_ID', 0);
}

$aiService = new \App\Services\AiService();

echo "Testing Gemini models...\n";
$geminiModels = $aiService->getAvailableModels('gemini', TENANT_ID);
print_r($geminiModels);

echo "\nTesting Grok models...\n";
$grokModels = $aiService->getAvailableModels('grok', TENANT_ID);
print_r($grokModels);
