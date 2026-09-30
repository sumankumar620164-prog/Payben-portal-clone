<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/fund_requests.php');
}
verify_csrf();

$db = get_db();
$request_id = (int) ($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

$stmt = $db->prepare("SELECT * FROM fund_requests WHERE id = ? AND status = 'pending'");
$stmt->execute([$request_id]);
$req = $stmt->fetch();

if (!$req) {
    flash('error', 'That request is no longer pending.');
    redirect('/admin/fund_requests.php');
}

if ($action === 'approve') {
    try {
        $db->beginTransaction();

        $credit = $db->prepare('UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?');
        $credit->execute([$req['amount'], $req['user_id']]);

        $update = $db->prepare(
            "UPDATE fund_requests SET status = 'approved', processed_at = NOW() WHERE id = ?"
        );
        $update->execute([$request_id]);

        $db->commit();
        flash('success', 'Request approved and wallet credited with ' . rupees((float) $req['amount']) . '.');
    } catch (Exception $e) {
        $db->rollBack();
        flash('error', 'Could not approve this request. Please try again.');
    }
} elseif ($action === 'reject') {
    $update = $db->prepare(
        "UPDATE fund_requests SET status = 'rejected', processed_at = NOW() WHERE id = ?"
    );
    $update->execute([$request_id]);
    flash('success', 'Request rejected.');
} else {
    flash('error', 'Invalid action.');
}

redirect('/admin/fund_requests.php');
