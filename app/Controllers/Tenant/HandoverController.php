<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\HandoverLogModel;
use App\Models\ConversationModel;
use App\Models\WaChannelModel;

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
}
