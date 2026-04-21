<?= view('_components/page_header', [
    'title' => 'Dashboard',
    'breadcrumb' => [['label' => 'Tenant', 'url' => '#'], ['label' => 'Dashboard']],
]) ?>

<div class="row">
    <div class="col-md-3">
        <?= view('_components/stat_card', [
            'icon' => 'bi-phone',
            'value' => $total_active_channels,
            'label' => 'Channel Aktif',
            'color' => 'primary',
        ]) ?>
    </div>
    <div class="col-md-3">
        <?= view('_components/stat_card', [
            'icon' => 'bi-chat-dots',
            'value' => $total_conversations_today,
            'label' => 'Percakapan Hari Ini',
            'color' => 'success',
        ]) ?>
    </div>
    <div class="col-md-3">
        <?= view('_components/stat_card', [
            'icon' => 'bi-exclamation-triangle',
            'value' => $total_handover_pending,
            'label' => 'Handover Pending',
            'color' => 'warning',
        ]) ?>
    </div>
    <div class="col-md-3">
        <?= view('_components/stat_card', [
            'icon' => 'bi-person-lines-fill',
            'value' => $total_handover_in_progress,
            'label' => 'Handover In Progress',
            'color' => 'info',
        ]) ?>
    </div>
</div>