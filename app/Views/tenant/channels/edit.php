<?= view('_components/page_header', [
    'title' => 'Edit WA Channel',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'WA Channels', 'url' => '/channels'],
        ['label' => 'Edit'],
    ],
]) ?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/channels/update/<?= $channel['id'] ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Channel Name</label>
                        <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                            id="name" name="name" value="<?= old('name', $channel['name']) ?>" required>
                        <?php if (session('errors.name')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.name') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="wa_number" class="form-label">WhatsApp Number</label>
                        <input type="text" class="form-control <?= session('errors.wa_number') ? 'is-invalid' : '' ?>"
                            id="wa_number" name="wa_number" value="<?= old('wa_number', $channel['wa_number']) ?>"
                            required>
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
                            id="fonnte_token" name="fonnte_token"
                            value="<?= old('fonnte_token', $channel['fonnte_token']) ?>" required>
                        <?php if (session('errors.fonnte_token')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.fonnte_token') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                <?= $channel['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Channel Aktif</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="/channels" class="btn btn-light">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Webhook Configuration</h6>
                <p class="small text-muted mb-2">Pastikan URL di bawah ini terpasang di Dashboard Fonnte Anda:</p>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control text-primary"
                        value="<?= base_url('webhook/' . $channel['uuid']) ?>" readonly id="webhook-copy">
                    <button class="btn btn-outline-secondary" type="button" onclick="copyWebhook()">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyWebhook() {
        const input = document.getElementById('webhook-copy');
        input.select();
        document.execCommand('copy');
        alert('Webhook URL copied to clipboard!');
    }
</script>