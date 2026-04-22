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

                        <?php if (isset($tenants)): ?>
                            <div class="mb-3">
                                <label for="tenant_id" class="form-label fw-bold">Tenant</label>
                                <select class="form-select" id="tenant_id" name="tenant_id" required>
                                    <option value="" disabled selected>Pilih Tenant</option>
                                    <?php foreach ($tenants as $tenant): ?>
                                        <option value="<?= $tenant['id'] ?>" <?= old('tenant_id') == $tenant['id'] ? 'selected' : '' ?>>
                                            <?= $tenant['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

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
                                        <?php foreach ($available_roles as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= old('role') === $value ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
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
                        <?php if (isset($available_roles['superadmin'])): ?>
                            <li class="mb-2"><strong>Administrator:</strong> Pemilik sistem (Global). Dapat mengelola semua tenant dan konfigurasi aplikasi.</li>
                        <?php endif; ?>
                        <?php if (isset($available_roles['tenant_admin'])): ?>
                            <li class="mb-2"><strong>Admin Tenant:</strong> Manajer Tenant. Mengelola KB, API Keys, nomor WA, dan user di dalam tenant ini.</li>
                        <?php endif; ?>
                        <li class="mb-2"><strong>Operator:</strong> Staf pemantau. Dapat melihat history percakapan dan daftar handover, namun tidak dapat mengubah konfigurasi.</li>
                        <li class="mb-2"><strong>Agent:</strong> Staf lapangan (Customer Service). Menangani chat customer (claim & close handover) dan membalas pesan secara langsung.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const tenantSelect = document.getElementById('tenant_id');
    const tenantDiv = tenantSelect ? tenantSelect.closest('.mb-3') : null;

    if (roleSelect && tenantDiv) {
        const toggleTenant = () => {
            if (roleSelect.value === 'superadmin') {
                tenantDiv.style.display = 'none';
                tenantSelect.removeAttribute('required');
            } else {
                tenantDiv.style.display = 'block';
                tenantSelect.setAttribute('required', 'required');
            }
        };

        roleSelect.addEventListener('change', toggleTenant);
        toggleTenant(); // Initial state
    }
});
</script>
