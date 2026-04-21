<?= view('_partials/header', ['title' => $title ?? 'Dashboard']) ?>
<?= view('_partials/navbar') ?>
<?php
$user = auth()->user();
$role = $user ? ($user->getGroups()[0] ?? 'agent') : 'agent';
$menu = config_menu('tenant', $role);
?>
<?= view('_partials/sidebar', ['menu' => $menu]) ?>

<main class="main-content">
    <?= view('_partials/flash_message') ?>
    <div class="content-wrapper">
        <?= $content ?>
    </div>
</main>

<?= view('_partials/footer') ?>