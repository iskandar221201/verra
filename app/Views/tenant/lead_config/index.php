<?= view('_components/page_header', [
    'title' => 'Lead Assignment Config',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Lead Config'],
    ],
]) ?>

<div class="row">
    <div class="col-md-8">
        <!-- Auto Assign Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0"><i class="bi bi-lightning-charge me-2"></i>Auto-Assign Settings</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('lead-config/update') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="lead_auto_assign"
                                name="lead_auto_assign" value="1" <?= ($config['lead_auto_assign'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="lead_auto_assign">
                                Aktifkan Auto-Assign Leads
                            </label>
                        </div>
                        <small class="text-muted">Otomatis assign customer baru ke salesperson via round-robin.</small>
                    </div>

                    <div class="mb-4">
                        <label for="lead_wa_group_id" class="form-label fw-bold">WhatsApp Group ID</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="lead_wa_group_id" id="lead_wa_group_id"
                                value="<?= esc($config['lead_wa_group_id'] ?? '') ?>" placeholder="120363xxxxxx@g.us">
                            <button type="button" class="btn btn-outline-secondary" id="btnFetchGroups"
                                data-bs-toggle="modal" data-bs-target="#fetchGroupModal">
                                <i class="bi bi-arrow-repeat me-1"></i> Fetch Groups
                            </button>
                        </div>
                        <small class="text-muted">Group ID dari Fonnte Dashboard (format: xxx@g.us).</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Salesperson Management -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-people me-2"></i>Daftar Salesperson</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSalespersonModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($salespersons)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-person-plus display-4 d-block mb-2"></i>
                        <p>Belum ada salesperson. Tambahkan untuk memulai round-robin assignment.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama</th>
                                    <th>No. WA</th>
                                    <th>Status</th>
                                    <th width="180">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="salespersonTable">
                                <?php foreach ($salespersons as $i => $sp): ?>
                                    <tr data-id="<?= $sp['id'] ?>">
                                        <td><span class="badge bg-secondary">
                                                <?= $sp['sort_order'] ?>
                                            </span></td>
                                        <td class="fw-bold">
                                            <?= esc($sp['name']) ?>
                                        </td>
                                        <td><code><?= esc($sp['wa_number']) ?></code></td>
                                        <td>
                                            <?php if ($sp['is_active']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                data-bs-target="#editSalespersonModal" data-id="<?= $sp['id'] ?>"
                                                data-name="<?= esc($sp['name']) ?>" data-wa="<?= esc($sp['wa_number']) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($sp['is_active']): ?>
                                                <a href="<?= base_url('lead-config/salesperson/delete/' . $sp['id']) ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Nonaktifkan salesperson ini?')">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url('lead-config/salesperson/activate/' . $sp['id']) ?>"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="return confirm('Aktifkan kembali salesperson ini?')">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-info"></i>Cara Kerja</h6>
                <ul class="small text-muted mb-0">
                    <li class="mb-2">Saat customer baru mengirim pesan pertama, sistem otomatis assign ke salesperson
                        berikutnya (round-robin)</li>
                    <li class="mb-2">Notifikasi dikirim ke WA Group berisi nama salesperson dan nomor customer</li>
                    <li class="mb-2">Urutan salesperson pada tabel menentukan urutan round-robin</li>
                    <li>Customer yang sudah pernah chat tidak akan di-assign ulang secara otomatis</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>Statistik</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Round-Robin Counter</span>
                    <span class="badge bg-primary">
                        <?= $config['lead_round_robin_counter'] ?? 0 ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Sales Aktif</span>
                    <span class="badge bg-success">
                        <?= count(array_filter($salespersons, fn($s) => $s['is_active'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Salesperson Modal -->
<div class="modal fade" id="addSalespersonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('lead-config/salesperson/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Salesperson</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama</label>
                        <input type="text" class="form-control" name="name" required placeholder="Nama salesperson">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">No. WhatsApp</label>
                        <input type="text" class="form-control" name="wa_number" required placeholder="628xxxxxxxxxx">
                        <small class="text-muted">Format: 628xxx (tanpa tanda +). Akan dinormalisasi otomatis.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Salesperson Modal -->
<div class="modal fade" id="editSalespersonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSalespersonForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Salesperson</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">No. WhatsApp</label>
                        <input type="text" class="form-control" name="wa_number" id="editWaNumber" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fetch Group Modal -->
<div class="modal fade" id="fetchGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fetch WA Groups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Channel</label>
                    <select class="form-select" id="fetchGroupChannel">
                        <option value="">-- Pilih Channel --</option>
                        <?php foreach ($channels as $ch): ?>
                            <option value="<?= $ch['id'] ?>">
                                <?= esc($ch['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" class="btn btn-primary w-100" id="btnDoFetchGroups">
                    <i class="bi bi-arrow-repeat me-1"></i> Fetch dari Fonnte
                </button>
                <div id="groupListContainer" class="mt-3" style="display:none;">
                    <label class="form-label fw-bold">Pilih Group</label>
                    <div id="groupList" class="list-group"></div>
                </div>
                <div id="fetchGroupLoading" class="text-center py-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Mengambil daftar group...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Edit Salesperson Modal population
    document.getElementById('editSalespersonModal').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const wa = btn.getAttribute('data-wa');

        document.getElementById('editName').value = name;
        document.getElementById('editWaNumber').value = wa;
        document.getElementById('editSalespersonForm').action = '<?= base_url('lead-config/salesperson/update') ?>/' + id;
    });

    // Fetch Groups
    document.getElementById('btnDoFetchGroups').addEventListener('click', function () {
        const channelId = document.getElementById('fetchGroupChannel').value;
        if (!channelId) {
            alert('Pilih channel terlebih dahulu.');
            return;
        }

        const loading = document.getElementById('fetchGroupLoading');
        const container = document.getElementById('groupListContainer');
        const groupList = document.getElementById('groupList');

        loading.style.display = 'block';
        container.style.display = 'none';
        groupList.innerHTML = '';

        const formData = new FormData();
        formData.append('channel_id', channelId);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('lead-config/fetch-groups') ?>', {
            method: 'POST',
            body: formData,
        })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success && data.groups && data.groups.length > 0) {
                    container.style.display = 'block';
                    data.groups.forEach(group => {
                        const name = group.name || group.subject || 'Unknown';
                        const id = group.id || '';
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                        item.innerHTML = `<span>${name}</span><code class="small">${id}</code>`;
                        item.addEventListener('click', function (ev) {
                            ev.preventDefault();
                            document.getElementById('lead_wa_group_id').value = id;
                            const modal = bootstrap.Modal.getInstance(document.getElementById('fetchGroupModal'));
                            modal.hide();
                        });
                        groupList.appendChild(item);
                    });
                } else {
                    container.style.display = 'block';
                    groupList.innerHTML = '<div class="text-muted text-center py-3">Tidak ada group ditemukan.</div>';
                }
            })
            .catch(() => {
                loading.style.display = 'none';
                alert('Gagal mengambil daftar group.');
            });
    });
</script>