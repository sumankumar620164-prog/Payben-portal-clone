<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/recharge.php');
}
verify_csrf();

$db = get_db();

$operator_id = (int) ($_POST['operator_id'] ?? 0);
$recharge_number = trim($_POST['recharge_number'] ?? '');
$amount = (float) ($_POST['amount'] ?? 0);

$stmt = $db->prepare("SELECT * FROM operators WHERE id = ? AND status = 'active'");
$stmt->execute([$operator_id]);
$operator = $stmt->fetch();

if (!$operator) {
    flash('error', 'Choose a valid operator.');
    redirect('/recharge.php');
}
if (!preg_match('/^[0-9]{6,15}$/', $recharge_number)) {
    flash('error', 'Enter a valid recharge number.');
    redirect('/recharge.php');
}
if ($amount < 10) {
    flash('error', 'Minimum recharge amount is ₹10.');
    redirect('/recharge.php');
}
if ($amount > (float) $user['wallet_balance']) {
    flash('error', 'Insufficient wallet balance for this recharge.');
    redirect('/recharge.php');
}

// --- Mock recharge gateway: ~85% success rate ---
$is_success = random_int(1, 100) <= 85;
$txn_ref = generate_txn_ref();

try {
    $db->beginTransaction();

    if ($is_success) {
        $commission = round($amount * ((float) $operator['commission_percent'] / 100), 2);

        // Deduct the recharge amount, guarding against a stale balance.
        $debit = $db->prepare(
            'UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ? AND wallet_balance >= ?'
        );
        $debit->execute([$amount, $user['id'], $amount]);

        if ($debit->rowCount() === 0) {
            $db->rollBack();
            flash('error', 'Insufficient wallet balance for this recharge.');
            redirect('/recharge.php');
        }

        // Credit the commission earned on this recharge.
        $credit = $db->prepare('UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?');
        $credit->execute([$commission, $user['id']]);

        $insert = $db->prepare(
            'INSERT INTO transactions (txn_ref, user_id, operator_id, recharge_number, amount, commission_earned, status)
             VALUES (?, ?, ?, ?, ?, ?, \'success\')'
        );
        $insert->execute([$txn_ref, $user['id'], $operator['id'], $recharge_number, $amount, $commission]);

        $db->commit();
        flash('success', "Recharge of " . rupees($amount) . " successful. Ref {$txn_ref} — you earned " . rupees($commission) . " commission.");
    } else {
        $insert = $db->prepare(
            'INSERT INTO transactions (txn_ref, user_id, operator_id, recharge_number, amount, commission_earned, status)
             VALUES (?, ?, ?, ?, ?, 0, \'failed\')'
        );
        $insert->execute([$txn_ref, $user['id'], $operator['id'], $recharge_number, $amount]);

        $db->commit();
        flash('error', "Recharge {$txn_ref} failed at the operator's end. Your wallet was not charged — please try again.");
    }
} catch (Exception $e) {
    $db->rollBack();
    flash('error', 'Something went wrong processing this recharge. Please try again.');
}

redirect('/transactions.php');
