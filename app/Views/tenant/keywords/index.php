<?= view('_components/page_header', [
    'title' => 'Handover Keywords',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Keywords']],
]) ?>

<div class="px-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Tambah Keyword Baru</h5>
                </div>
                <div class="card-body">
                    <form action="/keywords/store" method="post" class="row g-3">
                        <?= csrf_field() ?>
                        <div class="col-8">
                            <input type="text" class="form-control" name="keyword"
                                placeholder="Contoh: bantuan, halo, cs" required>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary w-100"
                                style="background-color: var(--color-primary);">
                                <i class="bi bi-plus-lg me-1"></i> Tambah
                            </button>
                        </div>
                    </form>
                    <div class="form-text mt-2">Pesan customer yang mengandung kata ini akan langsung memicu handover ke
                        agen.</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0">Daftar Keyword Aktif</h5>
                    <span class="badge bg-light text-dark rounded-pill">
                        <?= count($keywords) ?> Total
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Keyword</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($keywords)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Belum ada keyword.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($keywords as $keyword): ?>
                                        <tr class="<?= $keyword['is_active'] ? '' : 'table-light opacity-75' ?>">
                                            <td class="ps-4 fw-bold text-primary">
                                                <code><?= esc($keyword['keyword']) ?></code>
                                            </td>
                                            <td>
                                                <a href="/keywords/toggle/<?= $keyword['id'] ?>" class="text-decoration-none">
                                                    <?= view('_components/badge_status', [
                                                        'status' => $keyword['is_active'] ? 'active' : 'inactive',
                                                        'label' => $keyword['is_active'] ? 'Active' : 'Inactive'
                                                    ]) ?>
                                                </a>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="/keywords/delete/<?= $keyword['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Hapus keyword ini?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
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

        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2 text-warning"></i>Cara Kerja Keywords</h5>
                    <p class="text-muted small">
                        Verra akan memantau setiap pesan masuk dari customer. Jika isi pesan mengandung keyword yang
                        Anda daftarkan di sini (tidak harus sama persis, cukup mengandung kata tersebut), sistem akan
                        melakukan:
                    </p>
                    <ol class="small text-muted mb-4 ps-3">
                        <li class="mb-2">Membatalkan pemrosesan oleh AI secara otomatis.</li>
                        <li class="mb-2">Membuat tiket handover baru dengan status <span
                                class="badge bg-warning text-dark">Pending</span>.</li>
                        <li class="mb-2">Mengirim notifikasi ke panel agen agar segera diambil alih.</li>
                        <li class="mb-2">Kirim pesan otomatis ke customer: <i>"Menghubungkan Anda dengan agen
                                kami..."</i></li>
                    </ol>
                    <div class="alert alert-info border-0 shadow-none small mb-0">
                        <strong>Default Keywords:</strong> Saat pertama kali dibuka, sistem mengisi keyword umum:
                        <code>agent, cs, manusia, operator, bantuan, tolong</code>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>