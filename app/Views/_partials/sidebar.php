<?php
$current_url = '/' . (service('request')->getUri()->getSegment(1) ?? '');
?>
<aside class="sidebar position-fixed top-0 start-0 h-100 shadow-sm transition-all"
    style="background-color: var(--sidebar-bg); width: var(--sidebar-width); z-index: 1030; padding-top: var(--navbar-height);">
    <div class="sidebar-content py-3 h-100 overflow-y-auto">
        <div class="list-group list-group-flush">
            <?php foreach ($menu as $item):
                $is_active = ($current_url == $item['url'] || (isset($item['pattern']) && preg_match($item['pattern'], $current_url)));
                ?>
                <a href="<?= $item['url'] ?>"
                    class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center transition-all <?= $is_active ? 'active' : '' ?>"
                    style="<?= $is_active ? 'background-color: rgba(255,255,255,0.1); border-left: 4px solid var(--color-primary) !important;' : 'background: transparent; color: rgba(255,255,255,0.7);' ?>">
                    <i class="bi <?= $item['icon'] ?> fs-5 me-3"></i>
                    <span>
                        <?= $item['label'] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</aside>

<style>
    .sidebar .list-group-item.active {
        color: #fff !important;
    }

    .sidebar .list-group-item:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
        color: #fff !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }
</style>