<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? '/admin/dashboard.php' : '/dashboard.php');
}

$errors = [];
$old = ['full_name' => '', 'shop_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['shop_name'] = trim($_POST['shop_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $old['phone'])) $errors[] = 'Enter a valid 10-digit phone number.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $db = get_db();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? OR phone = ?');
        $stmt->execute([$old['email'], $old['phone']]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email or phone number already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = get_db()->prepare(
            'INSERT INTO users (role, full_name, shop_name, email, phone, password_hash, status)
             VALUES (\'retailer\', ?, ?, ?, ?, ?, \'pending\')'
        );
        $stmt->execute([
            $old['full_name'],
            $old['shop_name'] ?: null,
            $old['email'],
            $old['phone'],
            password_hash($password, PASSWORD_BCRYPT),
        ]);

        flash('success', 'Account created! An admin needs to approve it before you can sign in.');
        redirect('/login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create retailer account — Payben</title>
<link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card" style="max-width:480px;">
        <div class="brand">Payben</div>
        <div class="tagline">Become a recharge retailer</div>

        <?php foreach ($errors as $e): ?>
            <div class="notice notice-error"><?= h($e) ?></div>
        <?php endforeach; ?>

        <form method="post" action="<?= url('/register.php') ?>">
            <?= csrf_field() ?>
            <div class="field">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" required value="<?= h($old['full_name']) ?>">
            </div>
            <div class="field">
                <label for="shop_name">Shop name (optional)</label>
                <input type="text" id="shop_name" name="shop_name" value="<?= h($old['shop_name']) ?>">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= h($old['email']) ?>">
            </div>
            <div class="field">
                <label for="phone">Phone number</label>
                <input type="text" id="phone" name="phone" inputmode="numeric" required value="<?= h($old['phone']) ?>">
                <div class="hint">10 digits, this becomes your login ID.</div>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Create account</button>
        </form>

        <div class="auth-switch">
            Already registered? <a href="<?= url('/login.php') ?>">Sign in</a>
        </div>
    </div>
</div>
</body>
</html>
