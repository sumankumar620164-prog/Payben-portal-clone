<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? '/admin/dashboard.php' : '/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($phone === '' || $password === '') {
        $errors[] = 'Enter both phone number and password.';
    } else {
        $user = attempt_login($phone, $password);

        if (!$user) {
            $errors[] = 'Incorrect phone number or password.';
        } elseif ($user['role'] === 'retailer' && $user['status'] === 'pending') {
            log_out();
            $errors[] = 'Your account is awaiting admin approval. Please check back soon.';
        } elseif ($user['role'] === 'retailer' && $user['status'] === 'blocked') {
            log_out();
            $errors[] = 'This account has been blocked. Contact the portal admin.';
        } else {
            redirect($user['role'] === 'admin' ? '/admin/dashboard.php' : '/dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in — Payben</title>
<link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand">Payben</div>
        <div class="tagline">Recharge retailer ledger — sign in to continue</div>

        <?php foreach ($errors as $e): ?>
            <div class="notice notice-error"><?= h($e) ?></div>
        <?php endforeach; ?>

        <?php render_flashes(); ?>

        <form method="post" action="<?= url('/login.php') ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label for="phone">Phone number</label>
                <input type="text" id="phone" name="phone" inputmode="numeric" required autofocus value="<?= h($_POST['phone'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign in</button>
        </form>

        <div class="auth-switch">
            New retailer? <a href="<?= url('/register.php') ?>">Create an account</a>
        </div>
    </div>
</div>
</body>
</html>
