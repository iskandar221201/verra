<?= view('_components/page_header', [
    'title' => 'User Management',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Users']],
    'action' => ['label' => '+ Tambah User', 'url' => '/users/create'],
]) ?>

<div class="px-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <?= view('_components/empty_state', ['message' => 'Belum ada data user.']) ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="<?= $user->is_active ? '' : 'table-light opacity-75' ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 35px; height: 35px;">
                                                <span class="small fw-bold">
                                                    <?= strtoupper(substr($user->full_name ?? 'U', 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div class="fw-bold">
                                                <?= esc($user->full_name) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= esc($user->email) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $groups = $user->getGroups();
                                        $role = !empty($groups) ? $groups[0] : '-';
                                        ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= ucwords($role) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/users/toggle/<?= $user->id ?>" class="text-decoration-none">
                                            <?= view('_components/badge_status', [
                                                'status' => $user->is_active ? 'active' : 'inactive',
                                                'label' => $user->is_active ? 'Active' : 'Inactive'
                                            ]) ?>
                                        </a>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="/users/edit/<?= $user->id ?>" class="btn btn-sm btn-outline-primary"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
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