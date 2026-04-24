<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\LeadSalespersonModel;
use App\Models\WaChannelModel;
use App\Services\LeadAssignmentService;

class LeadAssignmentController extends BaseController
{
    protected LeadSalespersonModel $salespersonModel;
    protected WaChannelModel $channelModel;

    public function __construct()
    {
        $this->salespersonModel = new LeadSalespersonModel();
        $this->channelModel = new WaChannelModel();
    }

    /**
     * Manual assign via AJAX POST
     */
    public function assign()
    {
        $channelId = (int) $this->request->getPost('channel_id');
        $waNumber = $this->request->getPost('wa_number');
        $salespersonId = (int) $this->request->getPost('salesperson_id');

        if (empty($channelId) || empty($waNumber) || empty($salespersonId)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap.',
            ]);
        }

        // Validate channel belongs to tenant
        $channel = $this->channelModel->forTenant()->where('id', $channelId)->first();
        if (!$channel) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Channel tidak ditemukan.',
            ]);
        }

        $tenantId = $this->tenant_id ?? ($channel['tenant_id'] ?? 0);
        $userId = auth()->user()->id;
        $fonnteToken = $channel['fonnte_token'];

        $service = new LeadAssignmentService();
        $result = $service->manualAssign($tenantId, $channelId, $waNumber, $salespersonId, $userId, $fonnteToken);

        $statusCode = $result['success'] ? 200 : 400;
        return $this->response->setStatusCode($statusCode)->setJSON($result);
    }

    /**
     * Get active salespersons for the assign modal dropdown (AJAX GET)
     */
    public function getSalespersons()
    {
        $tenantId = $this->tenant_id ?? 0;

        $salespersons = $this->salespersonModel
            ->getActiveSorted($tenantId);

        return $this->response->setJSON([
            'success' => true,
            'salespersons' => $salespersons,
        ]);
    }
}
