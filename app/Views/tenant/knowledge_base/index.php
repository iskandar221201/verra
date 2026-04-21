<?= view('_components/page_header', [
    'title' => 'Knowledge Base',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Knowledge Base']],
    'action' => ['label' => '+ Tambah KB', 'url' => '/kb/create'],
]) ?>

<div class="px-4">
    <?php if (empty($groupedItems)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <?= view('_components/empty_state', ['message' => 'Belum ada data knowledge base.']) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="accordion" id="kbAccordion">
            <?php $i = 0;
            foreach ($groupedItems as $category => $items): ?>
                <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                    <h2 class="accordion-header" id="heading-<?= url_title($category) ?>">
                        <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?> fw-bold" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse-<?= url_title($category) ?>"
                            aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                            aria-controls="collapse-<?= url_title($category) ?>">
                            <i class="bi bi-folder2-open me-2 text-primary"></i>
                            <?= esc($category) ?>
                            <span class="badge bg-light text-dark ms-2">
                                <?= count($items) ?> items
                            </span>
                        </button>
                    </h2>
                    <div id="collapse-<?= url_title($category) ?>"
                        class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                        aria-labelledby="heading-<?= url_title($category) ?>" data-bs-parent="#kbAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Title</th>
                                            <th>Content Preview</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr class="<?= $item['is_active'] ? '' : 'table-light opacity-75' ?>">
                                                <td class="ps-4">
                                                    <div class="fw-bold">
                                                        <?= esc($item['title']) ?>
                                                    </div>
                                                    <small class="text-muted">Order:
                                                        <?= $item['sort_order'] ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 300px;">
                                                        <?= esc(word_limiter($item['content'], 10)) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="/kb/toggle/<?= $item['id'] ?>" class="text-decoration-none">
                                                        <?= view('_components/badge_status', [
                                                            'status' => $item['is_active'] ? 'active' : 'inactive',
                                                            'label' => $item['is_active'] ? 'Active' : 'Inactive'
                                                        ]) ?>
                                                    </a>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="btn-group">
                                                        <a href="/kb/edit/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary"
                                                            title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="/kb/delete/<?= $item['id'] ?>"
                                                            class="btn btn-sm btn-outline-danger" title="Hapus"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $i++; endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: var(--bs-primary);
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, .125);
    }
</style>