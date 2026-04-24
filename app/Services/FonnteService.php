<?php

namespace App\Services;

class FonnteService
{
    /**
     * Send a text message via Fonnte API
     *
     * @param string $fonnteToken Fonnte API token for the channel
     * @param string $waNumber Target WhatsApp number
     * @param string $message Message text to send
     * @return bool True on success, false on failure
     */
    public function send(string $fonnteToken, string $waNumber, string $message): bool
    {
        $url = 'https://api.fonnte.com/send';

        try {
            $client = \Config\Services::curlrequest();
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $fonnteToken,
                ],
                'form_params' => [
                    'target' => $waNumber,
                    'message' => $message,
                    'countryCode' => '62',
                ],
                'http_errors' => false,
                'verify' => env('CI_ENVIRONMENT') !== 'development',
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['status']) && $body['status'] === true) {
                return true;
            }

            // Log failure details
            $errorDetail = $body['reason'] ?? $body['message'] ?? "HTTP {$statusCode}";
            log_message('error', "[FonnteService] Send failed to {$waNumber}: {$errorDetail}");

            return false;
        } catch (\Exception $e) {
            log_message('error', "[FonnteService] Exception sending to {$waNumber}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send a text message to a WhatsApp group via Fonnte API
     *
     * @param string $fonnteToken Fonnte API token
     * @param string $groupId Group ID (format: xxx@g.us)
     * @param string $message Message text (supports @mention syntax)
     * @return bool True on success, false on failure
     */
    public function sendToGroup(string $fonnteToken, string $groupId, string $message): bool
    {
        $url = 'https://api.fonnte.com/send';

        try {
            $client = \Config\Services::curlrequest();
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => $fonnteToken,
                ],
                'form_params' => [
                    'target' => $groupId,
                    'message' => $message,
                ],
                'http_errors' => false,
                'verify' => env('CI_ENVIRONMENT') !== 'development',
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['status']) && $body['status'] === true) {
                return true;
            }

            $errorDetail = $body['reason'] ?? $body['message'] ?? "HTTP {$statusCode}";
            log_message('error', "[FonnteService] SendToGroup failed to {$groupId}: {$errorDetail}");

            return false;
        } catch (\Exception $e) {
            log_message('error', "[FonnteService] Exception sendToGroup {$groupId}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Fetch & refresh WhatsApp group list from Fonnte API
     *
     * @param string $fonnteToken Fonnte API token
     * @return array Group list or empty array on failure
     */
    public function fetchGroups(string $fonnteToken): array
    {
        try {
            $client = \Config\Services::curlrequest();

            // Step 1: Trigger refresh
            $client->request('POST', 'https://api.fonnte.com/fetch-group', [
                'headers' => ['Authorization' => $fonnteToken],
                'http_errors' => false,
                'verify' => env('CI_ENVIRONMENT') !== 'development',
            ]);

            // Step 2: Get the list
            $response = $client->request('POST', 'https://api.fonnte.com/get-whatsapp-group', [
                'headers' => ['Authorization' => $fonnteToken],
                'http_errors' => false,
                'verify' => env('CI_ENVIRONMENT') !== 'development',
            ]);

            $body = json_decode($response->getBody(), true);

            if (isset($body['status']) && $body['status'] === true && !empty($body['data'])) {
                return $body['data'];
            }

            return [];
        } catch (\Exception $e) {
            log_message('error', "[FonnteService] Exception fetchGroups: {$e->getMessage()}");
            return [];
        }
    }
}
