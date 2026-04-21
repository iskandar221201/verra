<?= view('_components/page_header', [
    'title' => 'Dashboard',
    'breadcrumb' => [['label' => 'Super Admin', 'url' => '#'], ['label' => 'Dashboard']],
]) ?>

<div class="row">
    <div class="col-md-3">
        <?= view('_components/stat_card', [
            'icon' => 'bi-building',
            'value' => $total_tenants,
            'label' => 'Total Tenants',
            'color' => 'primary',
        ]) ?>
    </div>
</div>