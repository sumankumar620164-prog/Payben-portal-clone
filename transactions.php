<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_role('retailer');
$db = get_db();

$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$sql = "SELECT t.*, o.name AS operator_name FROM transactions t
        JOIN operators o ON o.id = t.operator_id
        WHERE t.user_id = ?";
$params = [$user['id']];

if (in_array($status_filter, ['success', 'failed', 'pending'], true)) {
    $sql .= ' AND t.status = ?';
    $params[] = $status_filter;
}
if ($search !== '') {
    $sql .= ' AND (t.recharge_number LIKE ? OR t.txn_ref LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
$sql .= ' ORDER BY t.created_at DESC LIMIT 200';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$active_nav = 'transactions';
$page_title = 'Transactions';
require __DIR__ . '/includes/header.php';
?>

<span class="eyebrow">Retailer Ledger</span>
<h1>Transactions</h1>

<div class="card">
    <form method="get" action="<?= url('/transactions.php') ?>" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:end;">
        <div class="field" style="margin-bottom:0; min-width:180px;">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All</option>
                <option value="success" <?= $status_filter === 'success' ? 'selected' : '' ?>>Success</option>
                <option value="failed" <?= $status_filter === 'failed' ? 'selected' : '' ?>>Failed</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            </select>
        </div>
        <div class="field" style="margin-bottom:0; min-width:220px;">
            <label for="q">Search number / ref</label>
            <input type="text" id="q" name="q" value="<?= h($search) ?>">
        </div>
        <button type="submit" class="btn btn-ghost">Filter</button>
    </form>
</div>

<div class="card">
    <?php if (empty($rows)): ?>
        <div class="empty-state">No transactions match this filter.</div>
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
        <?php foreach ($rows as $t): ?>
            <tr>
                <td class="mono"><?= h($t['txn_ref']) ?></td>
                <td><?= h($t['operator_name']) ?></td>
                <td class="mono"><?= h($t['recharge_number']) ?></td>
                <td class="num"><?= rupees((float) $t['amount']) ?></td>
                <td class="num"><?= rupees((float) $t['commission_earned']) ?></td>
                <td><?= status_stamp($t['status']) ?></td>
                <td class="text-muted"><?= date('d M Y, g:i a', strtotime($t['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
