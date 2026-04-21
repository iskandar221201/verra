<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $users = $this->userModel->forTenant()->findAll();

        $data = [
            'title' => 'User Management',
            'users' => $users,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/users/index', $data),
        ]);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah User Baru',
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/users/create', $data),
        ]);
    }

    public function store()
    {
        $rules = [
            'email' => 'required|valid_email|is_unique[auth_identities.secret]',
            'full_name' => 'required|min_length[3]|max_length[255]',
            'role' => 'required|in_list[operator,agent]',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Create the user entity
        $user = new User([
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'full_name' => $this->request->getPost('full_name'),
            'tenant_id' => $this->tenant_id,
            'is_active' => 1,
        ]);

        if (!$this->userModel->save($user)) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan user.');
        }

        // Get the actual user object back
        $user = $this->userModel->findById($this->userModel->getInsertID());

        // Add to group
        $user->addGroup($this->request->getPost('role'));

        return redirect()->to(base_url('users'))->with('success', 'User berhasil dibuat.');
    }

    public function edit($id)
    {
        $user = $this->userModel->forTenant()->find($id);

        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit User',
            'user' => $user,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/users/edit', $data),
        ]);
    }

    public function update($id)
    {
        $user = $this->userModel->forTenant()->find($id);

        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[255]',
            'role' => 'required|in_list[operator,agent]',
            'password' => 'permit_empty|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Update basic info
        $user->full_name = $this->request->getPost('full_name');

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $user->password = $password;
        }

        if (!$this->userModel->save($user)) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui user.');
        }

        // Update group
        $user->syncGroups($this->request->getPost('role'));

        return redirect()->to(base_url('users'))->with('success', 'User berhasil diperbarui.');
    }

    public function toggleActive($id)
    {
        $user = $this->userModel->forTenant()->find($id);

        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        // Get current user to avoid self-deactivation if needed, 
        // but for now simple toggle
        $new_status = $user->is_active == 1 ? 0 : 1;
        $user->is_active = $new_status;

        if ($this->userModel->save($user)) {
            return redirect()->to(base_url('users'))->with('success', 'Status user berhasil diperbarui.');
        }

        return redirect()->to(base_url('users'))->with('error', 'Gagal memperbarui status user.');
    }
}
