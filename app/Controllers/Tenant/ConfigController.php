<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\TenantConfigModel;

class ConfigController extends BaseController
{
    protected $configModel;

    public function __construct()
    {
        $this->configModel = new TenantConfigModel();
    }

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
                'max_history' => 10
            ]);
            $config = $this->configModel->forTenant()->first();
        }

        $tenantId = defined('TENANT_ID') ? TENANT_ID : (auth()->user()->tenant_id ?? 0);

        $aiService = new \App\Services\AiService();
        $geminiModels = $aiService->getAvailableModels('gemini', $tenantId);
        $grokModels = $aiService->getAvailableModels('grok', $tenantId);

        $data = [
            'title' => 'Konfigurasi AI',
            'config' => $config,
            'gemini_models' => $geminiModels,
            'grok_models' => $grokModels,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/config/index', $data),
        ]);
    }

    public function update()
    {
        $config = $this->configModel->forTenant()->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Konfigurasi tidak ditemukan.');
        }

        $validation = [
            'ai_provider' => 'required|in_list[gemini,grok]',
            'gemini_model' => 'required|max_length[100]',
            'grok_model' => 'required|max_length[100]',
            'max_history' => 'required|is_natural_no_zero|less_than[51]',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'ai_provider' => $this->request->getPost('ai_provider'),
            'gemini_model' => $this->request->getPost('gemini_model'),
            'grok_model' => $this->request->getPost('grok_model'),
            'system_prompt' => $this->request->getPost('system_prompt'),
            'max_history' => $this->request->getPost('max_history'),
        ];

        if ($this->configModel->update($config['id'], $data)) {
            return redirect()->to(base_url('config'))->with('success', 'Konfigurasi berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui konfigurasi.');
    }
}
