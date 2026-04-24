<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\LeadSalespersonModel;
use App\Models\TenantConfigModel;
use App\Models\WaChannelModel;
use App\Services\FonnteService;

class LeadConfigController extends BaseController
{
    protected LeadSalespersonModel $salespersonModel;
    protected TenantConfigModel $configModel;
    protected WaChannelModel $channelModel;

    public function __construct()
    {
        $this->salespersonModel = new LeadSalespersonModel();
        $this->configModel = new TenantConfigModel();
        $this->channelModel = new WaChannelModel();
    }

    /**
     * Show lead config page: toggle, group ID, salesperson list
     */
    public function index()
    {
        $config = $this->configModel->forTenant()->first();

        // Create default config if not exists
        if (!$config) {
            $this->configModel->insert([
                'ai_provider' => 'gemini',
                'gemini_model' => 'gemini-1.5-flash',
                'grok_model' => 'grok-beta',
                'system_prompt' => 'Anda adalah asisten CS yang ramah.',
                'max_history' => 10,
                'lead_auto_assign' => 0,
                'lead_wa_group_id' => '',
                'lead_round_robin_counter' => 0,
            ]);
            $config = $this->configModel->forTenant()->first();
        }

        $salespersons = $this->salespersonModel->forTenant()
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $channels = $this->channelModel->forTenant()->findAll();

        $data = [
            'config' => $config,
            'salespersons' => $salespersons,
            'channels' => $channels,
        ];

        return view('_layouts/tenant', [
            'title' => 'Lead Assignment Config',
            'content' => view('tenant/lead_config/index', $data),
        ]);
    }

    /**
     * Update auto-assign toggle and group ID
     */
    public function updateConfig()
    {
        $config = $this->configModel->forTenant()->first();
        if (!$config) {
            return redirect()->back()->with('error', 'Konfigurasi tidak ditemukan.');
        }

        $autoAssign = $this->request->getPost('lead_auto_assign') ? 1 : 0;
        $groupId = trim($this->request->getPost('lead_wa_group_id') ?? '');

        // Validation: if auto-assign ON, group ID must be set
        if ($autoAssign && empty($groupId)) {
            return redirect()->back()->withInput()->with('error', 'WhatsApp Group ID wajib diisi jika Auto-Assign diaktifkan.');
        }

        $data = [
            'lead_auto_assign' => $autoAssign,
            'lead_wa_group_id' => $groupId,
        ];

        if ($this->configModel->update($config['id'], $data)) {
            return redirect()->to(base_url('lead-config'))->with('success', 'Konfigurasi Lead berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui konfigurasi.');
    }

    /**
     * Add a new salesperson
     */
    public function storeSalesperson()
    {
        $validation = [
            'name' => 'required|max_length[100]',
            'wa_number' => 'required|max_length[20]',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('phone');

        // Get next sort order
        $maxOrder = $this->salespersonModel->forTenant()
            ->selectMax('sort_order', 'max_order')
            ->first();
        $nextOrder = ($maxOrder['max_order'] ?? 0) + 1;

        $this->salespersonModel->insert([
            'name' => $this->request->getPost('name'),
            'wa_number' => normalizePhoneNumber($this->request->getPost('wa_number')),
            'sort_order' => $nextOrder,
            'is_active' => 1,
        ]);

        return redirect()->to(base_url('lead-config'))->with('success', 'Salesperson berhasil ditambahkan.');
    }

    /**
     * Update salesperson
     */
    public function updateSalesperson(int $id)
    {
        $salesperson = $this->salespersonModel->forTenant()->where('id', $id)->first();
        if (!$salesperson) {
            return redirect()->back()->with('error', 'Salesperson tidak ditemukan.');
        }

        $validation = [
            'name' => 'required|max_length[100]',
            'wa_number' => 'required|max_length[20]',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('phone');

        $this->salespersonModel->update($id, [
            'name' => $this->request->getPost('name'),
            'wa_number' => normalizePhoneNumber($this->request->getPost('wa_number')),
        ]);

        return redirect()->to(base_url('lead-config'))->with('success', 'Salesperson berhasil diperbarui.');
    }

    /**
     * Soft-delete (deactivate) salesperson
     */
    public function deleteSalesperson(int $id)
    {
        $salesperson = $this->salespersonModel->forTenant()->where('id', $id)->first();
        if (!$salesperson) {
            return redirect()->back()->with('error', 'Salesperson tidak ditemukan.');
        }

        $this->salespersonModel->update($id, ['is_active' => 0]);

        return redirect()->to(base_url('lead-config'))->with('success', 'Salesperson berhasil dinonaktifkan.');
    }

    /**
     * Reactivate salesperson
     */
    public function activateSalesperson(int $id)
    {
        $salesperson = $this->salespersonModel->forTenant()->where('id', $id)->first();
        if (!$salesperson) {
            return redirect()->back()->with('error', 'Salesperson tidak ditemukan.');
        }

        $this->salespersonModel->update($id, ['is_active' => 1]);

        return redirect()->to(base_url('lead-config'))->with('success', 'Salesperson berhasil diaktifkan kembali.');
    }

    /**
     * Update sort order via AJAX
     */
    public function updateOrder()
    {
        $order = $this->request->getJSON(true);

        if (!is_array($order)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid data']);
        }

        foreach ($order as $item) {
            if (isset($item['id'], $item['sort_order'])) {
                // Verify belongs to tenant
                $sp = $this->salespersonModel->forTenant()->where('id', $item['id'])->first();
                if ($sp) {
                    $this->salespersonModel->update($item['id'], ['sort_order' => (int) $item['sort_order']]);
                }
            }
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Urutan berhasil diperbarui.']);
    }

    /**
     * Fetch WA groups from Fonnte API (AJAX)
     */
    public function fetchGroups()
    {
        $channelId = $this->request->getPost('channel_id');

        if (empty($channelId)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Channel harus dipilih.']);
        }

        $channel = $this->channelModel->forTenant()->where('id', $channelId)->first();
        if (!$channel) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Channel tidak ditemukan.']);
        }

        $fonnteService = new FonnteService();
        $groups = $fonnteService->fetchGroups($channel['fonnte_token']);

        return $this->response->setJSON([
            'success' => true,
            'groups' => $groups,
        ]);
    }
}
