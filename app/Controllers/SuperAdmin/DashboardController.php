<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use App\Models\WaChannelModel;
use App\Models\ConversationModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $tenantModel = new TenantModel();
        $channelModel = new WaChannelModel();
        $conversationModel = new ConversationModel();

        $data = [
            'total_tenants' => $tenantModel->countAllResults(),
            'total_active_channels' => $channelModel->where('is_active', 1)->countAllResults(),
            'total_conversations' => $conversationModel->countAllResults(),
        ];

        return view('_layouts/superadmin', [
            'title' => 'Dashboard',
            'content' => view('superadmin/dashboard/index', $data),
        ]);
    }
}
