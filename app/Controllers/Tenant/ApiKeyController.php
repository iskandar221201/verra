<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\TenantApiKeyModel;

class ApiKeyController extends BaseController
{
    protected $apiKeyModel;

    public function __construct()
    {
        $this->apiKeyModel = new TenantApiKeyModel();
    }

    public function index()
    {
        $keys = $this->apiKeyModel->forTenant()
            ->orderBy('provider', 'ASC')
            ->orderBy('priority', 'ASC')
            ->findAll();

        $data = [
            'title' => 'API Keys',
            'keys' => $keys,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/api_keys/index', $data),
        ]);
    }

    public function store()
    {
        $validation = [
            'provider' => 'required|in_list[gemini,grok]',
            'label' => 'required|max_length[100]',
            'api_key' => 'required',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get max priority for this provider
        $maxPriority = $this->apiKeyModel->forTenant()
            ->where('provider', $this->request->getPost('provider'))
            ->selectMax('priority')
            ->first();

        $priority = ($maxPriority['priority'] ?? 0) + 1;

        $data = [
            'provider' => $this->request->getPost('provider'),
            'label' => $this->request->getPost('label'),
            'api_key' => $this->request->getPost('api_key'),
            'priority' => $priority,
            'is_active' => 1,
        ];

        if ($this->apiKeyModel->insert($data)) {
            return redirect()->to(base_url('api-keys'))->with('success', 'API Key berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan API Key.');
    }

    public function update($id)
    {
        $key = $this->apiKeyModel->forTenant()->find($id);

        if (!$key) {
            return redirect()->to(base_url('api-keys'))->with('error', 'API Key tidak ditemukan.');
        }

        $validation = [
            'label' => 'required|max_length[100]',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'label' => $this->request->getPost('label'),
            'is_active' => $this->request->getPost('is_active') ?? 0,
        ];

        // Only update API key if provided
        if ($this->request->getPost('api_key')) {
            $data['api_key'] = $this->request->getPost('api_key');
        }

        if ($this->apiKeyModel->update($id, $data)) {
            return redirect()->to(base_url('api-keys'))->with('success', 'API Key berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui API Key.');
    }

    public function delete($id)
    {
        $key = $this->apiKeyModel->forTenant()->find($id);

        if (!$key) {
            return redirect()->to(base_url('api-keys'))->with('error', 'API Key tidak ditemukan.');
        }

        if ($this->apiKeyModel->delete($id)) {
            return redirect()->to(base_url('api-keys'))->with('success', 'API Key berhasil dihapus.');
        }

        return redirect()->to(base_url('api-keys'))->with('error', 'Gagal menghapus API Key.');
    }

    public function updatePriority()
    {
        $priorities = $this->request->getJSON(true);

        if (!$priorities) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data']);
        }

        foreach ($priorities as $item) {
            $this->apiKeyModel->forTenant()->update($item['id'], ['priority' => $item['priority']]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }
}
