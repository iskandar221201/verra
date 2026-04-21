<?= view('_components/page_header', [
    'title' => 'Tambah WA Channel',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'WA Channels', 'url' => '/channels'],
        ['label' => 'Tambah'],
    ],
]) ?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/channels/store" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Channel Name</label>
                        <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                            id="name" name="name" value="<?= old('name') ?>" placeholder="Misal: CS Utama" required>
                        <?php if (session('errors.name')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.name') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="wa_number" class="form-label">WhatsApp Number</label>
                        <input type="text" class="form-control <?= session('errors.wa_number') ? 'is-invalid' : '' ?>"
                            id="wa_number" name="wa_number" value="<?= old('wa_number') ?>" placeholder="628123456789"
                            required>
                        <div class="form-text">Gunakan kode negara tanpa tanda +.</div>
                        <?php if (session('errors.wa_number')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.wa_number') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="fonnte_token" class="form-label">Fonnte API Token</label>
                        <input type="password"
                            class="form-control <?= session('errors.fonnte_token') ? 'is-invalid' : '' ?>"
                            id="fonnte_token" name="fonnte_token" required>
                        <div class="form-text">Dapatkan di dashboard <a href="https://fonnte.com"
                                target="_blank">Fonnte</a>.</div>
                        <?php if (session('errors.fonnte_token')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.fonnte_token') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="/channels" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Channel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-info border-0 shadow-sm">
            <h5 class="alert-heading"><i class="bi bi-info-circle-fill me-2"></i>Informasi Webhook</h5>
            <p>Setelah channel dibuat, Anda akan mendapatkan **Webhook URL** yang harus dipasangkan di dashboard Fonnte
                agar Verra bisa menerima pesan.</p>
            <hr>
            <p class="mb-0 small">Verra menggunakan UUID unik untuk setiap channel guna memastikan keamanan dan isolasi
                data antar tenant.</p>
        </div>
    </div>
</div>