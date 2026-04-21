<div class="card border-0 shadow-sm h-100 transition-all hover-lift">
    <div class="card-body p-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="rounded-pill p-3"
                    style="background-color: var(--color-<?= $color ?? 'primary' ?>); background-opacity: 0.1;">
                    <i class="bi <?= $icon ?> fs-3 text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <h3 class="mb-0 fw-bold">
                    <?= $value ?>
                </h3>
                <p class="text-muted mb-0 small">
                    <?= $label ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift:hover {
        transform: translateY(-5px);
    }
</style>