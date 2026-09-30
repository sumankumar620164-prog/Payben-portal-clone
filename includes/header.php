<?php
/**
 * Shared retailer layout. Include after require_login(), and set
 * $active_nav and $page_title before including this file.
 */
$active_nav = $active_nav ?? '';
$page_title = $page_title ?? 'Payben Retailer Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?> — Payben</title>
<link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">Payben<small>Retailer Ledger</small></div>

        <div class="wallet-chip">
            <div class="label">Wallet balance</div>
            <div class="amount"><?= rupees((float) $user['wallet_balance']) ?></div>
        </div>

        <nav class="main-nav">
            <a href="<?= url('/dashboard.php') ?>" class="<?= $active_nav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= url('/recharge.php') ?>" class="<?= $active_nav === 'recharge' ? 'active' : '' ?>">New Recharge</a>
            <a href="<?= url('/transactions.php') ?>" class="<?= $active_nav === 'transactions' ? 'active' : '' ?>">Transactions</a>
            <a href="<?= url('/wallet.php') ?>" class="<?= $active_nav === 'wallet' ? 'active' : '' ?>">Wallet</a>
            <a href="<?= url('/commission.php') ?>" class="<?= $active_nav === 'commission' ? 'active' : '' ?>">Commission</a>
            <a href="<?= url('/profile.php') ?>" class="<?= $active_nav === 'profile' ? 'active' : '' ?>">Profile</a>
        </nav>

        <div class="sidebar-footer">
            <div style="margin-bottom:.5rem;"><?= h($user['shop_name'] ?: $user['full_name']) ?></div>
            <a href="<?= url('/logout.php') ?>">Sign out →</a>
        </div>
    </aside>

    <main class="content">
        <?php render_flashes(); ?>
