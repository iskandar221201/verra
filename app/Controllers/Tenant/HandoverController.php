<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\HandoverLogModel;
use App\Models\ConversationModel;
use App\Models\WaChannelModel;
use App\Models\AgentMessageModel;

class HandoverController extends BaseController
{
    protected HandoverLogModel $handoverModel;
    protected ConversationModel $conversationModel;
    protected WaChannelModel $channelModel;

    public function __construct()
    {
        $this->handoverModel = new HandoverLogModel();
        $this->conversationModel = new ConversationModel();
        $this->channelModel = new WaChannelModel();
    }

    /**
     * List all handovers for the tenant
     */
    public function index()
    {
        $status = $this->request->getGet('status');
        $channelId = $this->request->getGet('channel_id');

        $query = $this->handoverModel->forTenant();

        if ($status) {
            $query->where('status', $status);
        }

        if ($channelId) {
            $query->where('channel_id', $channelId);
        }

        $handovers = $query->orderBy('created_at', 'DESC')->findAll();
        $channels = $this->channelModel->forTenant()->findAll();

        $data = [
            'title' => 'Handover List',
            'handovers' => $handovers,
            'channels' => $channels,
            'current_status' => $status,
            'current_channel' => $channelId,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/handover/index', $data),
        ]);
    }

    /**
     * Handover detail page
     */
    public function detail($id)
    {
        $handover = $this->handoverModel->forTenant()->find($id);

        if (!$handover) {
            return redirect()->to(base_url('handover'))->with('error', 'Handover tidak ditemukan.');
        }

        // Get conversation history
        $history = $this->conversationModel->getHistory(
            $handover['tenant_id'],
            $handover['channel_id'],
            $handover['wa_number'],
            50 // Show more in detail
        );

        $data = [
            'title' => 'Handover Detail: ' . $handover['wa_number'],
            'handover' => $handover,
            'history' => $history,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/handover/detail', $data),
        ]);
    }

    /**
     * Live Chat UI
     */
    public function chat()
    {
        // Get active handovers for sidebar
        $activeHandovers = $this->handoverModel->forTenant()
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Live Chat Agent',
            'activeHandovers' => $activeHandovers,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/handover/chat', $data),
        ]);
    }

    /**
     * JSON API: Get handover state (mode, status, claimed_by)
     */
    public function apiState($id)
    {
        $handover = $this->handoverModel->forTenant()->find($id);

        if (!$handover) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Handover tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'id' => (int) $handover['id'],
                'mode' => $handover['mode'] ?? 'ai',
                'handover_status' => $handover['status'],
                'claimed_by' => $handover['claimed_by'] ? (int) $handover['claimed_by'] : null,
                'wa_number' => $handover['wa_number'],
                'channel_id' => (int) $handover['channel_id'],
            ],
        ]);
    }

    /**
     * JSON API: Get chat history for a handover
     */
    public function apiHistory($id)
    {
        $handover = $this->handoverModel->forTenant()->find($id);

        if (!$handover) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Handover tidak ditemukan.',
            ]);
        }

        // Get conversation history
        $conversations = $this->conversationModel->getHistory(
            $handover['tenant_id'],
            $handover['channel_id'],
            $handover['wa_number'],
            50
        );

        // Get agent messages and merge
        $agentMessageModel = new AgentMessageModel();
        $agentMessages = $agentMessageModel
            ->where('handover_id', $id)
            ->orderBy('sent_at', 'ASC')
            ->findAll();

        // Build unified message list
        $messages = [];
        foreach ($conversations as $msg) {
            $messages[] = [
                'id' => (int) $msg['id'],
                'role' => $msg['role'],
                'message' => $msg['message'],
                'timestamp' => $msg['created_at'],
                'sender' => $msg['role'] === 'user' ? 'customer' : 'ai',
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $messages,
        ]);
    }
}
