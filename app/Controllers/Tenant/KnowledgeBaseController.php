<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\KnowledgeBaseModel;

class KnowledgeBaseController extends BaseController
{
    protected $kbModel;

    public function __construct()
    {
        $this->kbModel = new KnowledgeBaseModel();
    }

    public function index()
    {
        $items = $this->kbModel->forTenant()->orderBy('category', 'ASC')->orderBy('sort_order', 'ASC')->findAll();

        $groupedItems = [];
        foreach ($items as $item) {
            $groupedItems[$item['category']][] = $item;
        }

        $data = [
            'title' => 'Knowledge Base',
            'groupedItems' => $groupedItems,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/knowledge_base/index', $data),
        ]);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Knowledge Base',
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/knowledge_base/create', $data),
        ]);
    }

    public function store()
    {
        $validation = [
            'category' => 'required|max_length[100]',
            'title' => 'required|max_length[255]',
            'content' => 'required',
            'sort_order' => 'permit_empty|is_natural',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'category' => $this->request->getPost('category'),
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_active' => 1,
        ];

        if ($this->kbModel->insert($data)) {
            return redirect()->to(base_url('kb'))->with('success', 'Entry KB berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan entry KB.');
    }

    public function edit($id)
    {
        $item = $this->kbModel->forTenant()->find($id);

        if (!$item) {
            return redirect()->to(base_url('kb'))->with('error', 'Entry KB tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Knowledge Base',
            'item' => $item,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/knowledge_base/edit', $data),
        ]);
    }

    public function update($id)
    {
        $item = $this->kbModel->forTenant()->find($id);

        if (!$item) {
            return redirect()->to(base_url('kb'))->with('error', 'Entry KB tidak ditemukan.');
        }

        $validation = [
            'category' => 'required|max_length[100]',
            'title' => 'required|max_length[255]',
            'content' => 'required',
            'sort_order' => 'permit_empty|is_natural',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'category' => $this->request->getPost('category'),
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'sort_order' => $this->request->getPost('sort_order') ?: 0,
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        if ($this->kbModel->update($id, $data)) {
            return redirect()->to(base_url('kb'))->with('success', 'Entry KB berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui entry KB.');
    }

    public function delete($id)
    {
        $item = $this->kbModel->forTenant()->find($id);

        if (!$item) {
            return redirect()->to(base_url('kb'))->with('error', 'Entry KB tidak ditemukan.');
        }

        if ($this->kbModel->delete($id)) {
            return redirect()->to(base_url('kb'))->with('success', 'Entry KB berhasil dihapus.');
        }

        return redirect()->to(base_url('kb'))->with('error', 'Gagal menghapus entry KB.');
    }

    public function toggleActive($id)
    {
        $item = $this->kbModel->forTenant()->find($id);

        if (!$item) {
            return redirect()->to(base_url('kb'))->with('error', 'Entry KB tidak ditemukan.');
        }

        $new_status = $item['is_active'] == 1 ? 0 : 1;

        if ($this->kbModel->update($id, ['is_active' => $new_status])) {
            return redirect()->to(base_url('kb'))->with('success', 'Status entry KB berhasil diperbarui.');
        }

        return redirect()->to(base_url('kb'))->with('error', 'Gagal memperbarui status entry KB.');
    }
}
