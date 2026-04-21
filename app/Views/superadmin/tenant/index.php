<?= view('_components/page_header', [
    'title' => 'Tenants',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/superadmin/dashboard'], ['label' => 'Tenants']],
    'action' => ['label' => '+ Create Tenant', 'url' => '/superadmin/tenant/create'],
]) ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>UUID</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tenants)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <?= view('_components/empty_state', ['message' => 'No tenants found.']) ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tenants as $tenant): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        <?= esc($tenant['name']) ?>
                                    </div>
                                </td>
                                <td><code><?= esc($tenant['slug']) ?></code></td>
                                <td><small class="text-muted">
                                        <?= esc($tenant['uuid']) ?>
                                    </small></td>
                                <td>
                                    <?= view('_components/badge_status', [
                                        'status' => $tenant['is_active'] ? 'active' : 'inactive',
                                        'label' => $tenant['is_active'] ? 'Active' : 'Inactive'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($tenant['created_at'])) ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/superadmin/tenant/edit/<?= $tenant['id'] ?>"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="/superadmin/tenant/toggle-active/<?= $tenant['id'] ?>" method="POST"
                                            class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                class="btn btn-sm <?= $tenant['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                title="<?= $tenant['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                <i
                                                    class="bi <?= $tenant['is_active'] ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                                            </button>
                                        </form>
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