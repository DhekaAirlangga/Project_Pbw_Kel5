<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Jika sudah login sebagai admin, langsung ke dashboard admin
if (($_SESSION['auth_role'] ?? '') === 'admin') {
    header('Location: ../dashboard/dashboard_admin.php');
    exit;
}

$pesanError = $_SESSION['error'] ?? [];
$pesanSukses = $_SESSION['sukses'] ?? '';
unset($_SESSION['error'], $_SESSION['sukses']);

// Handle Form Submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['admin_username'] ?? '');
    $password = $_POST['admin_password'] ?? '';

    if ($username === '' || $password === '') {
        $pesanError[] = 'Username dan password admin wajib diisi.';
    } else {
        $stmt = getDB()->prepare('SELECT * FROM users_admin WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $pesanError[] = 'Username atau password salah.';
        } else {
            session_regenerate_id(true);
            $_SESSION['auth_role'] = 'admin';
            $_SESSION['user_data'] = [
                'nama' => $user['nama'],
                'role' => $user['role'],
            ];
            $_SESSION['sukses'] = 'Selamat bertugas! Dashboard Operasional Parkir UP terbuka.';
            header('Location: ../dashboard/dashboard_admin.php');
            exit;
        }
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator Petugas | Universitas Pancasila</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/Logo UP.png">
    <style>
        .auth-wrapper {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .auth-card {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .auth-header {
            background: var(--up-blue-subtle);
            border-bottom: 2px solid var(--up-blue-light);
            padding: 26px 20px;
            text-align: center;
        }
        .admin-badge-icon {
            width: 60px;
            height: 60px;
            background: var(--up-blue-light);
            color: var(--up-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 12px;
            border: 1px solid rgba(0, 73, 173, 0.2);
        }
        .auth-header h2 {
            font-size: 19px;
            font-weight: 800;
            color: var(--up-blue);
        }
        .auth-header p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 4px;
        }
        .auth-body {
            padding: 26px;
        }
    </style>
</head>
<body>

<header class="navbar-wrapper">
    <div class="navbar-container">
        <a href="../index.php" class="brand-link">
            <img src="../assets/images/Navbar.png" alt="Universitas Pancasila" class="brand-navbar-img">
            <div class="brand-divider"></div>
            <div class="brand-text-block">
                <span class="brand-text-top">PORTAL PETUGAS</span>
                <span class="brand-text-main">UNIVERSITAS PANCASILA</span>
            </div>
        </a>
        <a href="../index.php" class="btn btn-outline" style="font-size:13px;padding:6px 14px;">← Kembali ke Beranda</a>
    </div>
</header>

<main class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="admin-badge-icon">🛡️</div>
            <h2>Login Petugas & Administrator</h2>
            <p>Akses khusus pengelolaan operasional barrier gate parkir UP</p>
        </div>

        <?php if (count($pesanError) > 0): ?>
            <div style="padding:16px 26px 0;">
                <div class="alert-box danger" role="alert" style="margin-bottom:0;">
                    <span>⚠</span>
                    <div>
                        <?php foreach ($pesanError as $err): ?>
                            <div>• <?= e($err) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="auth-body">
            <form action="login_admin.php" method="post">
                <div class="form-group" style="margin-bottom:16px;">
                    <label for="admin_username">Username Petugas</label>
                    <input type="text" id="admin_username" name="admin_username" placeholder="Masukkan username admin" autocomplete="username" required>
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label for="admin_password">Password</label>
                    <div class="password-input-wrap">
                        <input type="password" id="admin_password" name="admin_password" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password" aria-pressed="false" aria-controls="admin_password">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:14.5px;">
                    Masuk ke Dashboard Operasional
                </button>
            </form>

        </div>
    </div>
</main>

<footer class="site-footer" style="padding:25px;text-align:center;">
    <div style="font-size:12.5px;color:#94a3b8;">
        PARKIRKU &copy; <?= date('Y') ?> Universitas Pancasila &middot; PBW Kelompok 5
    </div>
</footer>

<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const passwordInput = document.getElementById(button.getAttribute('aria-controls'));
            const shouldShow = passwordInput.type === 'password';
            passwordInput.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(shouldShow));
            button.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
            button.innerHTML = shouldShow
                ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1 0 2-.2 2.8-.5"/></svg>'
                : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
        });
    });
</script>

</body>
</html>
