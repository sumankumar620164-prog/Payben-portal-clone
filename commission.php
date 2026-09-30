<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');
$db = get_db();

$stmt = $db->prepare(
    "SELECT o.name AS operator_name, o.commission_percent,
            COUNT(*) AS txn_count,
            COALESCE(SUM(t.amount),0) AS total_amount,
            COALESCE(SUM(t.commission_earned),0) AS total_commission
     FROM transactions t
     JOIN operators o ON o.id = t.operator_id
     WHERE t.user_id = ? AND t.status = 'success'
     GROUP BY o.id, o.name, o.commission_percent
     ORDER BY total_commission DESC"
);
$stmt->execute([$user['id']]);
$rows = $stmt->fetchAll();

$grand_total = array_sum(array_column($rows, 'total_commission'));

$active_nav = 'commission';
$page_title = 'Commission';
require __DIR__ . '/includes/header.php';
?>

<span class="eyebrow">Retailer Ledger</span>
<h1>Commission earned</h1>

<div class="stat-row">
    <div class="stat-card">
        <div class="eyebrow">Total commission (lifetime)</div>
        <div class="value"><?= rupees((float) $grand_total) ?></div>
        <div class="sub">Across all successful recharges</div>
    </div>
</div>

<div class="card">
    <h2>By operator</h2>
    <?php if (empty($rows)): ?>
        <div class="empty-state">No commission earned yet — process a recharge to see it here.</div>
    <?php else: ?>
    <table class="ledger">
        <thead>
            <tr>
                <th>Operator</th>
                <th class="num">Rate</th>
                <th class="num">Recharges</th>
                <th class="num">Amount recharged</th>
                <th class="num">Commission earned</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= h($r['operator_name']) ?></td>
                <td class="num mono"><?= h($r['commission_percent']) ?>%</td>
                <td class="num mono"><?= (int) $r['txn_count'] ?></td>
                <td class="num"><?= rupees((float) $r['total_amount']) ?></td>
                <td class="num"><?= rupees((float) $r['total_commission']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
