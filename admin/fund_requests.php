<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_role('admin');
$db = get_db();

$status_filter = $_GET['status'] ?? 'pending';
$sql = "SELECT f.*, u.full_name, u.shop_name, u.phone
        FROM fund_requests f
        JOIN users u ON u.id = f.user_id
        WHERE 1=1";
$params = [];
if (in_array($status_filter, ['pending', 'approved', 'rejected'], true)) {
    $sql .= ' AND f.status = ?';
    $params[] = $status_filter;
}
$sql .= ' ORDER BY (f.status = "pending") DESC, f.created_at DESC LIMIT 200';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$active_nav = 'fund_requests';
$page_title = 'Fund Requests';
require __DIR__ . '/../includes/admin_header.php';
?>

<span class="eyebrow">Admin Register</span>
<h1>Wallet top-up requests</h1>

<div class="card">
    <form method="get" action="<?= url('/admin/fund_requests.php') ?>" style="display:flex; gap:1rem; align-items:end;">
        <div class="field" style="margin-bottom:0; min-width:200px;">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="">All</option>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Filter</button>
    </form>
</div>

<div class="card">
    <?php if (empty($requests)): ?>
        <div class="empty-state">No requests match this filter.</div>
    <?php else: ?>
    <table class="ledger">
        <thead>
            <tr>
                <th>Retailer</th>
                <th class="num">Amount</th>
                <th>Method</th>
                <th>Note</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td>
                    <div><?= h($r['shop_name'] ?: $r['full_name']) ?></div>
                    <div class="text-muted mono" style="font-size:.78rem;"><?= h($r['phone']) ?></div>
                </td>
                <td class="num"><?= rupees((float) $r['amount']) ?></td>
                <td><?= h($r['method']) ?></td>
                <td class="text-muted"><?= h($r['reference_note'] ?: '—') ?></td>
                <td><?= status_stamp($r['status']) ?></td>
                <td class="text-muted"><?= date('d M, g:i a', strtotime($r['created_at'])) ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                        <form method="post" action="<?= url('/admin/fund_request_action.php') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                        </form>
                        <form method="post" action="<?= url('/admin/fund_request_action.php') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
