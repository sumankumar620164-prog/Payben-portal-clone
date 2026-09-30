<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/wallet.php');
}
verify_csrf();

$amount = (float) ($_POST['amount'] ?? 0);
$method = trim($_POST['method'] ?? 'Bank Transfer (Demo)');
$note = trim($_POST['reference_note'] ?? '');

if ($amount < 100) {
    flash('error', 'Minimum top-up amount is ₹100.');
    redirect('/wallet.php');
}

$stmt = get_db()->prepare(
    'INSERT INTO fund_requests (user_id, amount, method, reference_note) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$user['id'], $amount, $method, $note ?: null]);

flash('success', 'Top-up request submitted for ' . rupees($amount) . '. It will reflect once the admin approves it.');
redirect('/wallet.php');
