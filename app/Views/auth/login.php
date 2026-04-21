<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= theme('app_name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= theme('font.url') ?>" rel="stylesheet">
    <style>
        :root {
            --color-primary:
                <?= theme('colors.primary') ?>
            ;
        }

        body {
            font-family: '<?= theme('font.family') ?>', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .card-header {
            background: var(--color-primary);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
            border: none;
        }

        .brand-logo {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
        }

        .brand-tagline {
            font-size: 0.85rem;
            opacity: 0.8;
            font-weight: 300;
        }

        .card-body {
            padding: 2.5rem;
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e3e6f0;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
            border-color: var(--color-primary);
        }

        .btn-primary {
            background: var(--color-primary);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            filter: brightness(90%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .alert {
            border-radius: 0.75rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="card-header">
            <div class="brand-logo"><?= theme('app_name') ?></div>
            <div class="brand-tagline"><?= theme('app_tagline') ?></div>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger mb-4">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label text-muted small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" id="email" value="<?= old('email') ?>"
                        placeholder="name@example.com" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label text-muted small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="••••••••"
                        required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>