<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_role('admin');
$db = get_db();

$editing = null;
if (!empty($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM operators WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$operators = $db->query('SELECT * FROM operators ORDER BY type, name')->fetchAll();

$active_nav = 'operators';
$page_title = 'Operators';
require __DIR__ . '/../includes/admin_header.php';
?>

<span class="eyebrow">Admin Register</span>
<h1>Operators &amp; commission rates</h1>

<div class="card" style="max-width:480px;">
    <h2><?= $editing ? 'Edit operator' : 'Add operator' ?></h2>
    <form method="post" action="<?= url('/admin/operator_save.php') ?>">
        <?= csrf_field() ?>
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
        <?php endif; ?>

        <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required value="<?= h($editing['name'] ?? '') ?>">
        </div>
        <div class="field">
            <label for="type">Type</label>
            <select id="type" name="type" required>
                <option value="mobile" <?= (($editing['type'] ?? '') === 'mobile') ? 'selected' : '' ?>>Mobile</option>
                <option value="dth" <?= (($editing['type'] ?? '') === 'dth') ? 'selected' : '' ?>>DTH</option>
            </select>
        </div>
        <div class="field">
            <label for="commission_percent">Commission %</label>
            <input type="number" id="commission_percent" name="commission_percent" step="0.01" min="0" max="100" required value="<?= h((string)($editing['commission_percent'] ?? '2.00')) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Save changes' : 'Add operator' ?></button>
        <?php if ($editing): ?>
            <a href="<?= url('/admin/operators.php') ?>" class="btn btn-ghost">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All operators</h2>
    <table class="ledger">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th class="num">Commission</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($operators as $o): ?>
            <tr>
                <td><?= h($o['name']) ?></td>
                <td class="text-muted"><?= h(ucfirst($o['type'])) ?></td>
                <td class="num mono"><?= h($o['commission_percent']) ?>%</td>
                <td><?= status_stamp($o['status']) ?></td>
                <td>
                    <a href="<?= url('/admin/operators.php') ?>?edit=<?= (int) $o['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                    <form method="post" action="<?= url('/admin/operator_save.php') ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                        <input type="hidden" name="toggle_status" value="1">
                        <button type="submit" class="btn btn-sm <?= $o['status'] === 'active' ? 'btn-danger' : 'btn-ghost' ?>">
                            <?= $o['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
