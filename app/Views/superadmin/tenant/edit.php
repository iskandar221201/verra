<?= view('_components/page_header', [
    'title' => 'Edit Tenant',
    'breadcrumb' => [['label' => 'Tenants', 'url' => '/superadmin/tenant'], ['label' => 'Edit']],
]) ?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/superadmin/tenant/update/<?= $tenant['id'] ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="uuid" class="form-label">UUID</label>
                        <input type="text" class="form-control" id="uuid" value="<?= esc($tenant['uuid']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Tenant Name</label>
                        <input type="text" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                            id="name" name="name" value="<?= old('name', $tenant['name']) ?>" required>
                        <?php if (session('errors.name')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.name') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control <?= session('errors.slug') ? 'is-invalid' : '' ?>"
                            id="slug" name="slug" value="<?= old('slug', $tenant['slug']) ?>" required>
                        <?php if (session('errors.slug')): ?>
                            <div class="invalid-feedback">
                                <?= session('errors.slug') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                <?= old('is_active', $tenant['is_active']) == '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="/superadmin/tenant" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>