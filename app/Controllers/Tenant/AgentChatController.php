<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Services\AgentChatService;

class AgentChatController extends BaseController
{
    protected AgentChatService $agentChatService;

    public function __construct()
    {
        $this->agentChatService = new AgentChatService();
    }

    /**
     * Claim a handover
     */
    public function claim($handoverId)
    {
        try {
            $agentId = auth()->id();
            if ($this->agentChatService->claim($handoverId, $agentId)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Berhasil mengambil alih percakapan.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'Gagal mengambil alih percakapan.'
        ]);
    }

    /**
     * Send message from agent
     */
    public function send($handoverId)
    {
        try {
            $agentId = auth()->id();
            $message = $this->request->getPost('message');

            if (empty($message)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Pesan tidak boleh kosong.'
                ]);
            }

            if ($this->agentChatService->sendMessage($handoverId, $agentId, $message)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Pesan terkirim.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'Gagal mengirim pesan.'
        ]);
    }

    /**
     * Return conversation to AI
     */
    public function returnToAi($handoverId)
    {
        try {
            if ($this->agentChatService->returnToAi($handoverId)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Percakapan berhasil dikembalikan ke AI.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'Gagal mengembalikan ke AI.'
        ]);
    }

    /**
     * Close handover
     */
    public function close($handoverId)
    {
        try {
            if ($this->agentChatService->close($handoverId)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Handover telah diselesaikan.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'Gagal menyelesaikan handover.'
        ]);
    }
}
