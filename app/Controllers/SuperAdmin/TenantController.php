<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use CodeIgniter\API\ResponseTrait;

class TenantController extends BaseController
{
    use ResponseTrait;

    protected $tenantModel;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
    }

    public function index()
    {
        $data = [
            'tenants' => $this->tenantModel->findAll(),
        ];

        return view('_layouts/superadmin', [
            'title' => 'Tenants',
            'content' => view('superadmin/tenant/index', $data),
        ]);
    }

    public function create()
    {
        return view('_layouts/superadmin', [
            'title' => 'Create Tenant',
            'content' => view('superadmin/tenant/create'),
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'slug' => 'permit_empty|alpha_dash|is_unique[tenants.slug]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = $this->request->getPost('name');
        $slug = $this->request->getPost('slug') ?: url_title($name, '-', true);

        // Ensure slug is unique if auto-generated
        if (empty($this->request->getPost('slug'))) {
            $originalSlug = $slug;
            $count = 1;
            while ($this->tenantModel->where('slug', $slug)->first()) {
                $slug = $originalSlug . '-' . $count++;
            }
        }

        $this->tenantModel->insert([
            'name' => $name,
            'slug' => $slug,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/superadmin/tenant')->with('message', 'Tenant created successfully.');
    }

    public function edit($id)
    {
        $tenant = $this->tenantModel->find($id);

        if (!$tenant) {
            return redirect()->to('/superadmin/tenant')->with('error', 'Tenant not found.');
        }

        $data = [
            'tenant' => $tenant,
        ];

        return view('_layouts/superadmin', [
            'title' => 'Edit Tenant',
            'content' => view('superadmin/tenant/edit', $data),
        ]);
    }

    public function update($id)
    {
        $tenant = $this->tenantModel->find($id);

        if (!$tenant) {
            return redirect()->to('/superadmin/tenant')->with('error', 'Tenant not found.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'slug' => "required|alpha_dash|is_unique[tenants.slug,id,{$id}]|max_length[100]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->tenantModel->update($id, [
            'name' => $this->request->getPost('name'),
            'slug' => $this->request->getPost('slug'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/superadmin/tenant')->with('message', 'Tenant updated successfully.');
    }

    public function toggleActive($id)
    {
        $tenant = $this->tenantModel->find($id);

        if (!$tenant) {
            return $this->failNotFound('Tenant not found.');
        }

        $this->tenantModel->update($id, [
            'is_active' => $tenant['is_active'] ? 0 : 1,
        ]);

        if ($this->request->isAJAX()) {
            return $this->respond(['success' => true]);
        }

        return redirect()->to('/superadmin/tenant')->with('message', 'Tenant status updated.');
    }
}
