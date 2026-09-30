<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_role('admin');
$db = get_db();

$status_filter = $_GET['status'] ?? '';
$sql = "SELECT * FROM users WHERE role = 'retailer'";
$params = [];
if (in_array($status_filter, ['pending', 'active', 'blocked'], true)) {
    $sql .= ' AND status = ?';
    $params[] = $status_filter;
}
$sql .= ' ORDER BY (status = "pending") DESC, created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$retailers = $stmt->fetchAll();

$active_nav = 'retailers';
$page_title = 'Retailers';
require __DIR__ . '/../includes/admin_header.php';
?>

<span class="eyebrow">Admin Register</span>
<h1>Retailers</h1>

<div class="card">
    <form method="get" action="<?= url('/admin/retailers.php') ?>" style="display:flex; gap:1rem; align-items:end;">
        <div class="field" style="margin-bottom:0; min-width:200px;">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending approval</option>
                <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="blocked" <?= $status_filter === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Filter</button>
    </form>
</div>

<div class="card">
    <?php if (empty($retailers)): ?>
        <div class="empty-state">No retailers match this filter.</div>
    <?php else: ?>
    <table class="ledger">
        <thead>
            <tr>
                <th>Name / Shop</th>
                <th>Phone</th>
                <th>Email</th>
                <th class="num">Wallet</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($retailers as $r): ?>
            <tr>
                <td>
                    <div><?= h($r['full_name']) ?></div>
                    <?php if ($r['shop_name']): ?><div class="text-muted" style="font-size:.8rem;"><?= h($r['shop_name']) ?></div><?php endif; ?>
                </td>
                <td class="mono"><?= h($r['phone']) ?></td>
                <td><?= h($r['email']) ?></td>
                <td class="num"><?= rupees((float) $r['wallet_balance']) ?></td>
                <td><?= status_stamp($r['status']) ?></td>
                <td class="text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                        <form method="post" action="<?= url('/admin/retailer_action.php') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                        </form>
                    <?php elseif ($r['status'] === 'active'): ?>
                        <form method="post" action="<?= url('/admin/retailer_action.php') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="action" value="block">
                            <button type="submit" class="btn btn-danger btn-sm">Block</button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="<?= url('/admin/retailer_action.php') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="action" value="unblock">
                            <button type="submit" class="btn btn-ghost btn-sm">Unblock</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
