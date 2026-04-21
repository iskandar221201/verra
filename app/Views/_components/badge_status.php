<?php
$badge_class = match ($status) {
    'active', 'handled' => 'success',
    'pending' => 'warning',
    'in_progress' => 'info',
    'inactive' => 'secondary',
    default => 'primary'
};
?>
<span class="badge rounded-pill border-0 px-3 py-2 text-capitalize"
    style="background-color: var(--color-<?= $badge_class ?>); font-weight: 500;">
    <?= str_replace('_', ' ', $status) ?>
</span>