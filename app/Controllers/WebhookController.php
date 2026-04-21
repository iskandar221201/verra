<?php

namespace App\Controllers;

use App\Models\WaChannelModel;
use App\Services\WebhookProcessorService;

class WebhookController extends BaseController
{
    /**
     * Receive incoming webhook from Fonnte
     * Public endpoint — no auth required
     *
     * @param string $channelUuid UUID of the WA channel
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function receive(string $channelUuid)
    {
        // 1. Find channel by UUID (no tenant filter — public endpoint)
        $channelModel = new WaChannelModel();
        $channel = $channelModel->where('uuid', $channelUuid)->first();

        // 2. Validate channel exists & is active
        if (!$channel || !$channel['is_active']) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Channel not found or inactive',
            ]);
        }

        // 3. Parse body: sender (nomor WA customer) and message (teks pesan)
        $sender = $this->request->getPost('sender') ?? $this->request->getVar('sender');
        $message = $this->request->getPost('message') ?? $this->request->getVar('message');

        // Validate required fields
        if (empty($sender) || empty($message)) {
            return $this->response->setStatusCode(200)->setJSON([
                'status' => true,
                'message' => 'No sender or message provided, skipped',
            ]);
        }

        // 4. Process the message
        try {
            $processor = new WebhookProcessorService();
            $processor->process($channel, $sender, $message);
        } catch (\Exception $e) {
            log_message('error', "[WebhookController] Error processing message: {$e->getMessage()}");
        }

        // 5. Return 200 (Fonnte doesn't care about response body)
        return $this->response->setStatusCode(200)->setJSON([
            'status' => true,
            'message' => 'OK',
        ]);
    }
}
