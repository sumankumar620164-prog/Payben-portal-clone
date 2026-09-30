<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');
$db = get_db();

$today_count = $db->prepare(
    "SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = 'success' AND DATE(created_at) = CURDATE()"
);
$today_count->execute([$user['id']]);
$today_count = (int) $today_count->fetchColumn();

$month_commission = $db->prepare(
    "SELECT COALESCE(SUM(commission_earned),0) FROM transactions
     WHERE user_id = ? AND status = 'success' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
);
$month_commission->execute([$user['id']]);
$month_commission = (float) $month_commission->fetchColumn();

$total_recharged = $db->prepare(
    "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id = ? AND status = 'success'"
);
$total_recharged->execute([$user['id']]);
$total_recharged = (float) $total_recharged->fetchColumn();

$recent = $db->prepare(
    "SELECT t.*, o.name AS operator_name FROM transactions t
     JOIN operators o ON o.id = t.operator_id
     WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT 8"
);
$recent->execute([$user['id']]);
$recent = $recent->fetchAll();

$active_nav = 'dashboard';
$page_title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head">
    <div>
        <span class="eyebrow">Retailer Ledger</span>
        <h1>Welcome back, <?= h($user['full_name']) ?></h1>
    </div>
    <a href="<?= url('/recharge.php') ?>" class="btn btn-primary">+ New Recharge</a>
</div>

<div class="stat-row">
    <div class="stat-card">
        <div class="eyebrow">Recharges today</div>
        <div class="value"><?= $today_count ?></div>
        <div class="sub">Successful, since midnight</div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Commission this month</div>
        <div class="value"><?= rupees($month_commission) ?></div>
        <div class="sub"><?= date('F Y') ?></div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Lifetime recharged</div>
        <div class="value"><?= rupees($total_recharged) ?></div>
        <div class="sub">All successful transactions</div>
    </div>
</div>

<div class="card">
    <div class="section-head">
        <h2>Recent activity</h2>
        <a href="<?= url('/transactions.php') ?>" class="text-muted">View all →</a>
    </div>

    <?php if (empty($recent)): ?>
        <div class="empty-state">No recharges yet. Your first entry will show up here.</div>
    <?php else: ?>
    <table class="ledger">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Operator</th>
                <th>Number</th>
                <th class="num">Amount</th>
                <th class="num">Commission</th>
                <th>Status</th>
                <th>When</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $t): ?>
            <tr>
                <td class="mono"><?= h($t['txn_ref']) ?></td>
                <td><?= h($t['operator_name']) ?></td>
                <td class="mono"><?= h($t['recharge_number']) ?></td>
                <td class="num"><?= rupees((float) $t['amount']) ?></td>
                <td class="num"><?= rupees((float) $t['commission_earned']) ?></td>
                <td><?= status_stamp($t['status']) ?></td>
                <td class="text-muted"><?= date('d M, g:i a', strtotime($t['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
