<?= view('_components/page_header', [
    'title' => 'Tambah User Baru',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Users', 'url' => '/users'],
        ['label' => 'Tambah'],
    ],
]) ?>

<div class="px-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="/users/store" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                value="<?= old('full_name') ?>" placeholder="Nama lengkap user" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>"
                                placeholder="email@contoh.com" required>
                            <div class="form-text">Email harus unik dan akan digunakan untuk login.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label fw-bold">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="" disabled selected>Pilih Role</option>
                                        <option value="operator" <?= old('role') === 'operator' ? 'selected' : '' ?>
                                            >Operator</option>
                                        <option value="agent" <?= old('role') === 'agent' ? 'selected' : '' ?>>Agent
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">Minimal 8 karakter.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="/users" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4"
                                style="background-color: var(--color-primary);">Simpan User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Tentang Role</h5>
                    <ul class="mb-0 ps-3 small">
                        <li class="mb-2"><strong>Operator:</strong> Dapat melihat history percakapan dan daftar
                            handover, namun tidak dapat mengubah konfigurasi.</li>
                        <li class="mb-2"><strong>Agent:</strong> Dikhususkan untuk menangani chat customer (claim &
                            close handover) dan membalas pesan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>