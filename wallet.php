<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');
$db = get_db();

$requests = $db->prepare(
    'SELECT * FROM fund_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 20'
);
$requests->execute([$user['id']]);
$requests = $requests->fetchAll();

$active_nav = 'wallet';
$page_title = 'Wallet';
require __DIR__ . '/includes/header.php';
?>

<span class="eyebrow">Retailer Ledger</span>
<h1>Wallet</h1>

<div class="stat-row">
    <div class="stat-card">
        <div class="eyebrow">Current balance</div>
        <div class="value"><?= rupees((float) $user['wallet_balance']) ?></div>
        <div class="sub">Available for recharges</div>
    </div>
</div>

<div class="card" style="max-width:520px;">
    <h2>Request a top-up</h2>
    <p class="text-muted" style="margin-top:-.4rem;">Demo mode: no real payment gateway is called. Submitting creates a request the admin approves manually, crediting your wallet.</p>
    <form method="post" action="<?= url('/fund_request_submit.php') ?>">
        <?= csrf_field() ?>
        <div class="field">
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" min="100" step="1" required>
        </div>
        <div class="field">
            <label for="method">Method</label>
            <select id="method" name="method">
                <option>Bank Transfer (Demo)</option>
                <option>UPI (Demo)</option>
                <option>Cash Deposit (Demo)</option>
            </select>
        </div>
        <div class="field">
            <label for="reference_note">Reference note (optional)</label>
            <input type="text" id="reference_note" name="reference_note" maxlength="150" placeholder="e.g. UTR number">
        </div>
        <button type="submit" class="btn btn-primary">Submit request</button>
    </form>
</div>

<div class="card">
    <h2>Request history</h2>
    <?php if (empty($requests)): ?>
        <div class="empty-state">No top-up requests yet.</div>
    <?php else: ?>
    <table class="ledger">
        <thead>
            <tr>
                <th class="num">Amount</th>
                <th>Method</th>
                <th>Note</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td class="num"><?= rupees((float) $r['amount']) ?></td>
                <td><?= h($r['method']) ?></td>
                <td class="text-muted"><?= h($r['reference_note'] ?: '—') ?></td>
                <td><?= status_stamp($r['status']) ?></td>
                <td class="text-muted"><?= date('d M, g:i a', strtotime($r['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
