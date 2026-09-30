<?php
/**
 * Shared admin layout. Include after require_role('admin'), and set
 * $active_nav and $page_title before including this file.
 */
$active_nav = $active_nav ?? '';
$page_title = $page_title ?? 'Payben Admin';

$pending_funds = (int) get_db()->query(
    "SELECT COUNT(*) FROM fund_requests WHERE status = 'pending'"
)->fetchColumn();
$pending_retailers = (int) get_db()->query(
    "SELECT COUNT(*) FROM users WHERE role = 'retailer' AND status = 'pending'"
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?> — Payben Admin</title>
<link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">Payben<small>Admin Register</small></div>

        <nav class="main-nav" style="margin-top:1.4rem;">
            <a href="<?= url('/admin/dashboard.php') ?>" class="<?= $active_nav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= url('/admin/retailers.php') ?>" class="<?= $active_nav === 'retailers' ? 'active' : '' ?>">
                Retailers <?= $pending_retailers > 0 ? '<span class="pill">' . $pending_retailers . ' new</span>' : '' ?>
            </a>
            <a href="<?= url('/admin/transactions.php') ?>" class="<?= $active_nav === 'transactions' ? 'active' : '' ?>">Transactions</a>
            <a href="<?= url('/admin/fund_requests.php') ?>" class="<?= $active_nav === 'fund_requests' ? 'active' : '' ?>">
                Fund Requests <?= $pending_funds > 0 ? '<span class="pill">' . $pending_funds . ' pending</span>' : '' ?>
            </a>
            <a href="<?= url('/admin/operators.php') ?>" class="<?= $active_nav === 'operators' ? 'active' : '' ?>">Operators</a>
        </nav>

        <div class="sidebar-footer">
            <div style="margin-bottom:.5rem;"><?= h($admin['full_name']) ?></div>
            <a href="<?= url('/logout.php') ?>">Sign out →</a>
        </div>
    </aside>

    <main class="content">
        <?php render_flashes(); ?>
