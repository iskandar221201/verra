<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= theme('app_name') ?> -
        <?= $title ?? 'No Title' ?>
    </title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?= theme('font.url') ?>" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --color-primary: <?= theme('colors.primary') ?>;
            --color-secondary: <?= theme('colors.secondary') ?>;
            --color-success: <?= theme('colors.success') ?>;
            --color-danger: <?= theme('colors.danger') ?>;
            --color-warning: <?= theme('colors.warning') ?>;
            --color-info: <?= theme('colors.info') ?>;
            --color-dark: <?= theme('colors.dark') ?>;
            --sidebar-bg: <?= theme('colors.sidebar_bg') ?>;
            --navbar-bg: <?= theme('colors.navbar_bg') ?>;
            --font-family: '<?= theme('font.family') ?>', sans-serif;
            --sidebar-width: <?= theme('layout.sidebar_width') ?>;
            --sidebar-collapsed-width: <?= theme('layout.sidebar_collapsed_width') ?>;
            --navbar-height: <?= theme('layout.navbar_height') ?>;
        }

        body {
            font-family: var(--font-family);
            background-color: #f8f9fa;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--navbar-height);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>