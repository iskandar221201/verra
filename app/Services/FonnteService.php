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
}
