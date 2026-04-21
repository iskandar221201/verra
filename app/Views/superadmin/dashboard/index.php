<?= view('_components/page_header', [
    'title' => 'Dashboard',
    'breadcrumb' => [['label' => 'Super Admin', 'url' => '#'], ['label' => 'Dashboard']],
]) ?>

<div class="row">
    <div class="col-md-4">
        <?= view('_components/stat_card', [
            'icon' => 'bi-building',
            'value' => $total_tenants,
            'label' => 'Total Tenant',
            'color' => 'primary',
        ]) ?>
    </div>
    <div class="col-md-4">
        <?= view('_components/stat_card', [
            'icon' => 'bi-phone',
            'value' => $total_active_channels,
            'label' => 'Channel Aktif',
            'color' => 'success',
        ]) ?>
    </div>
    <div class="col-md-4">
        <?= view('_components/stat_card', [
            'icon' => 'bi-chat-dots',
            'value' => $total_conversations,
            'label' => 'Total Percakapan',
            'color' => 'info',
        ]) ?>
    </div>
</div>