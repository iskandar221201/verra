<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\WaChannelModel;

class ChannelController extends BaseController
{
    protected $channelModel;

    public function __construct()
    {
        $this->channelModel = new WaChannelModel();
    }

    public function index()
    {
        $data = [
            'title' => 'WA Channels',
            'channels' => $this->channelModel->forTenant()->findAll(),
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/channels/index', $data),
        ]);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah WA Channel',
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/channels/create', $data),
        ]);
    }

    public function store()
    {
        $validation = [
            'name' => 'required|max_length[100]',
            'wa_number' => 'required|max_length[20]',
            'fonnte_token' => 'required|max_length[255]',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'wa_number' => $this->request->getPost('wa_number'),
            'fonnte_token' => $this->request->getPost('fonnte_token'),
            'is_active' => 1,
        ];

        if ($this->channelModel->insert($data)) {
            return redirect()->to(base_url('channels'))->with('success', 'Channel berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan channel.');
    }

    public function edit($id)
    {
        $channel = $this->channelModel->forTenant()->find($id);

        if (!$channel) {
            return redirect()->to(base_url('channels'))->with('error', 'Channel tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit WA Channel',
            'channel' => $channel,
        ];

        return view('_layouts/tenant', [
            'title' => $data['title'],
            'content' => view('tenant/channels/edit', $data),
        ]);
    }

    public function update($id)
    {
        $channel = $this->channelModel->forTenant()->find($id);

        if (!$channel) {
            return redirect()->to(base_url('channels'))->with('error', 'Channel tidak ditemukan.');
        }

        $validation = [
            'name' => 'required|max_length[100]',
            'wa_number' => 'required|max_length[20]',
            'fonnte_token' => 'required|max_length[255]',
        ];

        if (!$this->validate($validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'wa_number' => $this->request->getPost('wa_number'),
            'fonnte_token' => $this->request->getPost('fonnte_token'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        if ($this->channelModel->update($id, $data)) {
            return redirect()->to(base_url('channels'))->with('success', 'Channel berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui channel.');
    }

    public function delete($id)
    {
        $channel = $this->channelModel->forTenant()->find($id);

        if (!$channel) {
            return redirect()->to(base_url('channels'))->with('error', 'Channel tidak ditemukan.');
        }

        if ($this->channelModel->delete($id)) {
            return redirect()->to(base_url('channels'))->with('success', 'Channel berhasil dihapus.');
        }

        return redirect()->to(base_url('channels'))->with('error', 'Gagal menghapus channel.');
    }
}
