<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_role('admin');
$db = get_db();

$active_retailers = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='retailer' AND status='active'")->fetchColumn();
$pending_retailers = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='retailer' AND status='pending'")->fetchColumn();
$today_txns = (int) $db->query("SELECT COUNT(*) FROM transactions WHERE status='success' AND DATE(created_at)=CURDATE()")->fetchColumn();
$today_volume = (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status='success' AND DATE(created_at)=CURDATE()")->fetchColumn();
$commission_paid = (float) $db->query("SELECT COALESCE(SUM(commission_earned),0) FROM transactions WHERE status='success'")->fetchColumn();
$wallet_float = (float) $db->query("SELECT COALESCE(SUM(wallet_balance),0) FROM users WHERE role='retailer'")->fetchColumn();

$recent = $db->query(
    "SELECT t.*, o.name AS operator_name, u.shop_name, u.full_name
     FROM transactions t
     JOIN operators o ON o.id = t.operator_id
     JOIN users u ON u.id = t.user_id
     ORDER BY t.created_at DESC LIMIT 10"
)->fetchAll();

$active_nav = 'dashboard';
$page_title = 'Dashboard';
require __DIR__ . '/../includes/admin_header.php';
?>

<span class="eyebrow">Admin Register</span>
<h1>Portal overview</h1>

<div class="stat-row">
    <div class="stat-card">
        <div class="eyebrow">Active retailers</div>
        <div class="value"><?= $active_retailers ?></div>
        <div class="sub"><?= $pending_retailers ?> awaiting approval</div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Recharges today</div>
        <div class="value"><?= $today_txns ?></div>
        <div class="sub"><?= rupees($today_volume) ?> volume</div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Commission paid out</div>
        <div class="value"><?= rupees($commission_paid) ?></div>
        <div class="sub">Lifetime, all retailers</div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Wallet float</div>
        <div class="value"><?= rupees($wallet_float) ?></div>
        <div class="sub">Held across retailer wallets</div>
    </div>
</div>

<div class="card">
    <div class="section-head">
        <h2>Recent transactions (all retailers)</h2>
        <a href="<?= url('/admin/transactions.php') ?>" class="text-muted">View all →</a>
    </div>

    <?php if (empty($recent)): ?>
        <div class="empty-state">No transactions yet.</div>
    <?php else: ?>
    <table class="ledger">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Retailer</th>
                <th>Operator</th>
                <th class="num">Amount</th>
                <th>Status</th>
                <th>When</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $t): ?>
            <tr>
                <td class="mono"><?= h($t['txn_ref']) ?></td>
                <td><?= h($t['shop_name'] ?: $t['full_name']) ?></td>
                <td><?= h($t['operator_name']) ?></td>
                <td class="num"><?= rupees((float) $t['amount']) ?></td>
                <td><?= status_stamp($t['status']) ?></td>
                <td class="text-muted"><?= date('d M, g:i a', strtotime($t['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
