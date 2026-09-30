<?php
/**
 * Run this once in your browser after importing schema.sql, e.g.
 *   http://localhost/payben-portal/seed_admin.php
 * It creates the first admin login. Delete this file afterwards —
 * it refuses to run again once an admin already exists, but there's
 * no reason to leave it reachable.
 */
require_once __DIR__ . '/config/database.php';

$db = get_db();
$existing = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

if ($existing > 0) {
    die('An admin account already exists. Nothing to do — you can delete this file.');
}

$phone = '9999999999';
$password = 'admin123';

$stmt = $db->prepare(
    "INSERT INTO users (role, full_name, email, phone, password_hash, status)
     VALUES ('admin', 'Portal Admin', '[email protected]', ?, ?, 'active')"
);
$stmt->execute([$phone, password_hash($password, PASSWORD_BCRYPT)]);

echo "Admin account created.<br>";
echo "Phone: {$phone}<br>";
echo "Password: {$password}<br><br>";
echo "<strong>Sign in at /login.php, then delete seed_admin.php.</strong>";
