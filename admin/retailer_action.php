<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/retailers.php');
}
verify_csrf();

$user_id = (int) ($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

$map = [
    'approve' => 'active',
    'block'   => 'blocked',
    'unblock' => 'active',
];

if (!isset($map[$action]) || $user_id <= 0) {
    flash('error', 'Invalid action.');
    redirect('/admin/retailers.php');
}

$db = get_db();
$stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'retailer'");
$stmt->execute([$map[$action], $user_id]);

$labels = ['approve' => 'approved', 'block' => 'blocked', 'unblock' => 'unblocked'];
flash('success', "Retailer {$labels[$action]}.");
redirect('/admin/retailers.php');
