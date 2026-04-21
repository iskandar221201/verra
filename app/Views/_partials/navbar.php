<nav class="navbar navbar-expand-lg fixed-top shadow-sm"
    style="background-color: var(--navbar-bg); height: var(--navbar-height);">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="<?= theme('logo_path') ?>" alt="Logo" width="30" height="30"
                class="d-inline-block align-text-top me-2">
            <span class="fw-bold" style="color: var(--color-primary);">
                <?= theme('app_name') ?>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                        data-bs-toggle="dropdown">
                        <div class="avatar-sm me-2 d-none d-sm-block">
                            <i class="bi bi-person-circle fs-4"></i>
                        </div>
                        <span>
                            <span><?= auth()->user()->username ?? 'Guest' ?></span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/logout"><i
                                    class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>