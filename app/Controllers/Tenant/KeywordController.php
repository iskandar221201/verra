<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\HandoverKeywordModel;

class KeywordController extends BaseController
{
    protected $keywordModel;

    public function __construct()
    {
        $this->keywordModel = new HandoverKeywordModel();
    }

    public function index()
    {
        $keywords = $this->keywordModel->forTenant()->findAll();

        // Seed default keywords if empty
        if (empty($keywords)) {
            $defaults = ['agent', 'cs', 'manusia', 'operator', 'bantuan', 'tolong'];
            foreach ($defaults as $keyword) {
                $this->keywordModel->insert([
                    'keyword' => $keyword,
                    'is_active' => 1,
                ]);
            }
            $keywords = $this->keywordModel->forTenant()->findAll();
        }

        $data = [
            'title' => 'Handover Keywords',
            'keywords' => $keywords,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/keywords/index', $data),
        ]);
    }

    public function store()
    {
        $validation = [
            'keyword' => 'required|max_length[100]',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'keyword' => strtolower(trim($this->request->getPost('keyword'))),
            'is_active' => 1,
        ];

        // Check if exists
        $exists = $this->keywordModel->forTenant()->where('keyword', $data['keyword'])->first();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Keyword sudah ada.');
        }

        if ($this->keywordModel->insert($data)) {
            return redirect()->to(base_url('keywords'))->with('success', 'Keyword berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan keyword.');
    }

    public function delete($id)
    {
        $keyword = $this->keywordModel->forTenant()->find($id);

        if (!$keyword) {
            return redirect()->to(base_url('keywords'))->with('error', 'Keyword tidak ditemukan.');
        }

        if ($this->keywordModel->delete($id)) {
            return redirect()->to(base_url('keywords'))->with('success', 'Keyword berhasil dihapus.');
        }

        return redirect()->to(base_url('keywords'))->with('error', 'Gagal menghapus keyword.');
    }

    public function toggleActive($id)
    {
        $keyword = $this->keywordModel->forTenant()->find($id);

        if (!$keyword) {
            return redirect()->to(base_url('keywords'))->with('error', 'Keyword tidak ditemukan.');
        }

        $new_status = $keyword['is_active'] == 1 ? 0 : 1;

        if ($this->keywordModel->update($id, ['is_active' => $new_status])) {
            return redirect()->to(base_url('keywords'))->with('success', 'Status keyword berhasil diperbarui.');
        }

        return redirect()->to(base_url('keywords'))->with('error', 'Gagal memperbarui status keyword.');
    }
}
