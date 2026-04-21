<?= view('_components/page_header', [
    'title' => 'API Keys',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'API Keys']],
    'action' => ['label' => '+ Tambah API Key', 'url' => '#', 'attr' => 'data-bs-toggle="modal" data-bs-target="#modalAddKey"']
]) ?>

<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex">
        <div class="me-3 fs-4">
            <i class="bi bi-info-circle-fill"></i>
        </div>
        <div>
            <h6 class="alert-heading fw-bold">Tentang Prioritas API Key</h6>
            <p class="mb-0 small">
                Verra akan mencoba menggunakan API Key dengan <strong>prioritas tertinggi (Priority 1)</strong> terlebih
                dahulu.
                Jika key tersebut error atau kuota habis, Verra akan otomatis mencoba key berikutnya.
                Tarik-lepas (drag and drop) baris tabel untuk mengatur urutan prioritas.
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-api-keys">
                        <thead class="bg-light">
                            <tr>
                                <th width="50" class="ps-4">#</th>
                                <th>Provider</th>
                                <th>Label</th>
                                <th>API Key (Masked)</th>
                                <th>Status</th>
                                <th>Last Used</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-keys">
                            <?php if (empty($keys)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <?= view('_components/empty_state', ['message' => 'Belum ada API key.']) ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($keys as $key): ?>
                                    <tr data-id="<?= $key['id'] ?>" class="sortable-row">
                                        <td class="ps-4">
                                            <i class="bi bi-grip-vertical text-muted cursor-move me-2"></i>
                                            <span class="priority-number">
                                                <?= esc($key['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($key['provider'] == 'gemini'): ?>
                                                <span class="badge bg-primary px-2">Gemini</span>
                                            <?php else: ?>
                                                <span class="badge bg-dark px-2">Grok</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?= esc($key['label']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-muted">****<?= substr($key['api_key'], -4) ?></code>
                                        </td>
                                        <td>
                                            <?= view('_components/badge_status', [
                                                'status' => $key['is_active'] ? 'active' : 'inactive',
                                                'label' => $key['is_active'] ? 'Active' : 'Inactive'
                                            ]) ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= $key['last_used_at'] ? date('d M Y H:i', strtotime($key['last_used_at'])) : '-' ?>
                                            </small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit"
                                                    data-id="<?= $key['id'] ?>" data-label="<?= esc($key['label']) ?>"
                                                    data-provider="<?= $key['provider'] ?>"
                                                    data-active="<?= $key['is_active'] ?>" data-bs-toggle="modal"
                                                    data-bs-target="#modalEditKey">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="<?= base_url('api-keys/delete/' . $key['id']) ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Hapus API key ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add -->
<div class="modal fade" id="modalAddKey" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('api-keys/store') ?>" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Provider</label>
                        <select class="form-select" name="provider" required>
                            <option value="gemini">Google Gemini</option>
                            <option value="grok">xAI Grok</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Label</label>
                        <input type="text" class="form-control" name="label" required placeholder="Contoh: Key Utama">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">API Key</label>
                        <input type="password" class="form-control" name="api_key" required
                            placeholder="Masukkan API key asli">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditKey" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditKey" action="" method="POST">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Label</label>
                        <input type="text" class="form-control" name="label" id="edit_label" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">API Key (Opsional)</label>
                        <input type="password" class="form-control" name="api_key"
                            placeholder="Isi hanya jika ingin ganti key">
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_active" value="1">
                        <label class="form-check-label fw-bold" for="edit_active">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .cursor-move {
        cursor: move;
    }

    .sortable-ghost {
        opacity: 0.4;
        background-color: #f8f9fa;
    }
</style>

<!-- Load Sortable.js -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    // Edit Modal Data
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const label = this.getAttribute('data-label');
            const active = this.getAttribute('data-active');

            document.getElementById('formEditKey').action = '<?= base_url('api-keys/update') ?>/' + id;
            document.getElementById('edit_label').value = label;
            document.getElementById('edit_active').checked = (active == 1);
        });
    });

    // Sortable Implementation
    const el = document.getElementById('sortable-keys');
    if (el && el.querySelectorAll('.sortable-row').length > 1) {
        Sortable.create(el, {
            animation: 150,
            handle: '.bi-grip-vertical',
            ghostClass: 'sortable-ghost',
            onEnd: function () {
                updatePriorities();
            }
        });
    }

    function updatePriorities() {
        const rows = document.querySelectorAll('.sortable-row');
        const data = [];

        rows.forEach((row, index) => {
            const newPriority = index + 1;
            row.querySelector('.priority-number').innerText = newPriority;
            data.push({
                id: row.getAttribute('data-id'),
                priority: newPriority
            });
        });

        fetch('<?= base_url('api-keys/update-priority') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                '<?= config('App')->CSRFHeaderName ?>': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    // Optional: Show toast
                } else {
                    alert('Gagal mengupdate prioritas.');
                }
            });
    }
</script>