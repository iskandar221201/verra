<div class="text-center py-5 px-4 bg-white rounded shadow-sm">
    <div class="mb-4">
        <i class="bi <?= $icon ?? 'bi-folder2-open' ?> text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
    </div>
    <h4 class="fw-bold">
        <?= $title ?>
    </h4>
    <p class="text-muted mx-auto" style="max-width: 400px;">
        <?= $message ?>
    </p>
    <?php if (isset($action_url)): ?>
        <a href="<?= $action_url ?>" class="btn btn-primary px-4 py-2 mt-3 shadow-sm border-0"
            style="background-color: var(--color-primary);">
            <?= $action_label ?? 'Create New' ?>
        </a>
    <?php endif; ?>
</div>