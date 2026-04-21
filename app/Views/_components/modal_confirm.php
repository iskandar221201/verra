<div class="modal fade" id="<?= $id ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <?= $title ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted mb-0">
                    <?= $message ?>
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <form action="<?= $confirm_url ?>" method="<?= $method ?? 'POST' ?>">
                    <button type="submit" class="btn btn-<?= $color ?? 'danger' ?>">
                        <?= $confirm_label ?? 'Confirm' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>