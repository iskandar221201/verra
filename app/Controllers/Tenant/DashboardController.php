<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\WaChannelModel;
use App\Models\ConversationModel;
use App\Models\HandoverLogModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $channelModel = new WaChannelModel();
        $conversationModel = new ConversationModel();
        $handoverModel = new HandoverLogModel();

        $data = [
            'total_active_channels' => $channelModel->where('tenant_id', $this->tenant_id)
                ->where('is_active', 1)
                ->countAllResults(),
            'total_conversations_today' => $conversationModel->where('tenant_id', $this->tenant_id)
                ->where('created_at >=', date('Y-m-d 00:00:00'))
                ->countAllResults(),
            'total_handover_pending' => $handoverModel->where('tenant_id', $this->tenant_id)
                ->where('status', 'pending')
                ->countAllResults(),
            'total_handover_in_progress' => $handoverModel->where('tenant_id', $this->tenant_id)
                ->where('status', 'in_progress')
                ->countAllResults(),
        ];

        return view('_layouts/tenant', [
            'title' => 'Dashboard',
            'content' => view('tenant/dashboard/index', $data),
        ]);
    }
}
