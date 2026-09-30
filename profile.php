<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');
$db = get_db();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $shop_name = trim($_POST['shop_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($full_name === '') $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

        if (empty($errors)) {
            $check = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $check->execute([$email, $user['id']]);
            if ($check->fetch()) {
                $errors[] = 'That email is already in use by another account.';
            }
        }

        if (empty($errors)) {
            $stmt = $db->prepare('UPDATE users SET full_name = ?, shop_name = ?, email = ? WHERE id = ?');
            $stmt->execute([$full_name, $shop_name ?: null, $email, $user['id']]);
            flash('success', 'Profile updated.');
            redirect('/profile.php');
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } else {
            $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);
            flash('success', 'Password changed.');
            redirect('/profile.php');
        }
    }

    // Re-fetch so the form reflects the latest state (or shows old values on error)
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $user = $stmt->fetch();
}

$active_nav = 'profile';
$page_title = 'Profile';
require __DIR__ . '/includes/header.php';
?>

<span class="eyebrow">Retailer Ledger</span>
<h1>Profile</h1>

<?php foreach ($errors as $e): ?>
    <div class="notice notice-error"><?= h($e) ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:520px;">
    <h2>Account details</h2>
    <form method="post" action="<?= url('/profile.php') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">
        <div class="field">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" required value="<?= h($user['full_name']) ?>">
        </div>
        <div class="field">
            <label for="shop_name">Shop name</label>
            <input type="text" id="shop_name" name="shop_name" value="<?= h($user['shop_name']) ?>">
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?= h($user['email']) ?>">
        </div>
        <div class="field">
            <label>Phone number</label>
            <input type="text" value="<?= h($user['phone']) ?>" disabled>
            <div class="hint">Phone number is your login ID and can't be changed here.</div>
        </div>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
</div>

<div class="card" style="max-width:520px;">
    <h2>Change password</h2>
    <form method="post" action="<?= url('/profile.php') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">
        <div class="field">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="field">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="field">
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Update password</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
