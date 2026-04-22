<div class="d-md-flex align-items-center justify-content-between mb-4 px-4 pt-4">
    <div class="mb-3 mb-md-0">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <?php foreach ($breadcrumb as $i => $item): ?>
                    <li class="breadcrumb-item <?= $i == count($breadcrumb) - 1 ? 'active' : '' ?>">
                        <?php if ($i < count($breadcrumb) - 1): ?>
                            <a href="<?= $item['url'] ?>" class="text-decoration-none text-muted">
                                <?= $item['label'] ?>
                            </a>
                        <?php else: ?>
                            <?= $item['label'] ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <h2 class="fw-bold h3 mb-0">
            <?= $title ?>
        </h2>
    </div>
    <?php if (isset($action)): ?>
        <a href="<?= $action['url'] ?>" class="btn btn-primary d-flex align-items-center shadow-sm border-0 px-4 py-2"
            style="background-color: var(--color-primary);" <?= $action['attr'] ?? '' ?>>
            <i class="bi <?= $action['icon'] ?? 'bi-plus' ?> me-2"></i>
            <?= $action['label'] ?>
        </a>
    <?php endif; ?>
</div>