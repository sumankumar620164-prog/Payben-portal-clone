<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/operators.php');
}
verify_csrf();

$db = get_db();
$id = (int) ($_POST['id'] ?? 0);

if (!empty($_POST['toggle_status']) && $id > 0) {
    $stmt = $db->prepare('SELECT status FROM operators WHERE id = ?');
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();

    if ($current !== false) {
        $new_status = $current === 'active' ? 'inactive' : 'active';
        $update = $db->prepare('UPDATE operators SET status = ? WHERE id = ?');
        $update->execute([$new_status, $id]);
        flash('success', 'Operator status updated.');
    }
    redirect('/admin/operators.php');
}

$name = trim($_POST['name'] ?? '');
$type = $_POST['type'] ?? '';
$commission_percent = (float) ($_POST['commission_percent'] ?? 0);

if ($name === '' || !in_array($type, ['mobile', 'dth'], true) || $commission_percent < 0 || $commission_percent > 100) {
    flash('error', 'Check the operator details and try again.');
    redirect('/admin/operators.php');
}

if ($id > 0) {
    $stmt = $db->prepare('UPDATE operators SET name = ?, type = ?, commission_percent = ? WHERE id = ?');
    $stmt->execute([$name, $type, $commission_percent, $id]);
    flash('success', 'Operator updated.');
} else {
    $stmt = $db->prepare('INSERT INTO operators (name, type, commission_percent) VALUES (?, ?, ?)');
    $stmt->execute([$name, $type, $commission_percent]);
    flash('success', 'Operator added.');
}

redirect('/admin/operators.php');
