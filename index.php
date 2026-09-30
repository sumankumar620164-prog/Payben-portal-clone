<?php
require_once __DIR__ . '/includes/auth.php';

$user = current_user();
if ($user) {
    redirect($user['role'] === 'admin' ? '/admin/dashboard.php' : '/dashboard.php');
}
redirect('/login.php');
