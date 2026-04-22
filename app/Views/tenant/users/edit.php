<?= view('_components/page_header', [
    'title' => 'Edit User',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Users', 'url' => '/users'],
        ['label' => 'Edit'],
    ],
]) ?>

<div class="px-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="/users/update/<?= $user->id ?>" method="post">
                        <?= csrf_field() ?>

                        <?php if (isset($tenants)): ?>
                            <div class="mb-3">
                                <label for="tenant_id" class="form-label fw-bold">Tenant</label>
                                <select class="form-select" id="tenant_id" name="tenant_id" required>
                                    <?php foreach ($tenants as $tenant): ?>
                                        <option value="<?= $tenant['id'] ?>" <?= old('tenant_id', $user->tenant_id) == $tenant['id'] ? 'selected' : '' ?>>
                                            <?= $tenant['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                value="<?= old('full_name', $user->full_name) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control bg-light" value="<?= esc($user->email) ?>" disabled>
                            <div class="form-text">Email tidak dapat diubah.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label fw-bold">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <?php
                                        $groups = $user->getGroups();
                                        $currentRole = !empty($groups) ? $groups[0] : '';
                                        $selectedRole = old('role', $currentRole);
                                        ?>
                                        <?php foreach ($available_roles as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $selectedRole === $value ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">Password Baru (Opsional)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="/users" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4"
                                style="background-color: var(--color-primary);">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Status Akun</h5>
                    <p class="small mb-3">Jika user sudah tidak aktif, Anda dapat menonaktifkan akunnya melalui halaman
                        daftar user agar mereka tidak dapat lagi mengakses dashboard.</p>
                    <a href="/users/toggle/<?= $user->id ?>" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-power me-2"></i>
                        <?= $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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