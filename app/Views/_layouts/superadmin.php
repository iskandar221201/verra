<?= view('_partials/header', ['title' => $title ?? 'SuperAdmin Dashboard']) ?>
<?= view('_partials/navbar') ?>
<?php
$menu = config_menu('superadmin', 'superadmin');
?>
<?= view('_partials/sidebar', ['menu' => $menu]) ?>

<main class="main-content">
    <?= view('_partials/flash_message') ?>
    <div class="content-wrapper">
        <?= $content ?>
    </div>
</main>

<?= view('_partials/footer') ?>