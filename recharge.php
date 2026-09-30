<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');
$db = get_db();

$operators = $db->query(
    "SELECT * FROM operators WHERE status = 'active' ORDER BY type, name"
)->fetchAll();

$mobile_ops = array_filter($operators, fn($o) => $o['type'] === 'mobile');
$dth_ops = array_filter($operators, fn($o) => $o['type'] === 'dth');

$active_nav = 'recharge';
$page_title = 'New Recharge';
require __DIR__ . '/includes/header.php';
?>

<span class="eyebrow">Retailer Ledger</span>
<h1>New recharge entry</h1>
<p class="text-muted">This is a demo processor — no real telecom or payment network is contacted. Recharges succeed or fail at random so you can see the full flow.</p>

<div class="card" style="max-width:520px;">
    <form method="post" action="<?= url('/recharge_process.php') ?>">
        <?= csrf_field() ?>

        <div class="field">
            <label for="operator_id">Operator</label>
            <select id="operator_id" name="operator_id" required>
                <option value="">Select an operator</option>
                <optgroup label="Mobile">
                    <?php foreach ($mobile_ops as $o): ?>
                        <option value="<?= (int) $o['id'] ?>"><?= h($o['name']) ?> — <?= h($o['commission_percent']) ?>% commission</option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="DTH">
                    <?php foreach ($dth_ops as $o): ?>
                        <option value="<?= (int) $o['id'] ?>"><?= h($o['name']) ?> — <?= h($o['commission_percent']) ?>% commission</option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>

        <div class="field">
            <label for="recharge_number">Mobile / DTH subscriber number</label>
            <input type="text" id="recharge_number" name="recharge_number" inputmode="numeric" maxlength="15" required placeholder="10-digit number">
        </div>

        <div class="field">
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" min="10" max="<?= (int) $user['wallet_balance'] ?: 0 ?>" step="1" required>
            <div class="hint">Wallet balance: <?= rupees((float) $user['wallet_balance']) ?></div>
        </div>

        <button type="submit" class="btn btn-primary">Process recharge</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
