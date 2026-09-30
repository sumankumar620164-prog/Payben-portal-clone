<?php

/** Format a number as Indian Rupees, e.g. 1234.5 -> ₹1,234.50 */
function rupees(float $amount): string
{
    return '₹' . number_format($amount, 2);
}

/** Generate a unique-looking transaction reference like PB7F3K9Q2L */
function generate_txn_ref(): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $ref = 'PB';
    for ($i = 0; $i < 8; $i++) {
        $ref .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $ref;
}

/** Store a one-time flash message in the session */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Pull and clear any queued flash messages */
function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/** Render flash messages as HTML (call once per page, near the top of <main>) */
function render_flashes(): void
{
    foreach (get_flashes() as $f) {
        $type = htmlspecialchars($f['type']);
        $msg = htmlspecialchars($f['message']);
        echo "<div class=\"notice notice-{$type}\">{$msg}</div>";
    }
}

/** CSRF token: create if missing, return it */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** CSRF token as a ready-to-print hidden input */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/** Verify a submitted CSRF token, halting the request if it doesn't match */
function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        http_response_code(403);
        die('Security check failed. Please go back and try again.');
    }
}

/**
 * Build a link/action URL that works whether the app sits at the web
 * root or in a subfolder (e.g. XAMPP's http://localhost/payben-portal/).
 * BASE_URL is auto-detected in config/database.php.
 */
function url(string $path = ''): string
{
    return BASE_URL . $path;
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/** Escape for safe HTML output */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** A small status badge (ledger "stamp") used across retailer and admin views */
function status_stamp(string $status): string
{
    $status = strtolower($status);
    $labels = [
        'success'  => 'PAID',
        'failed'   => 'FAILED',
        'pending'  => 'PENDING',
        'approved' => 'APPROVED',
        'rejected' => 'REJECTED',
        'active'   => 'ACTIVE',
        'blocked'  => 'BLOCKED',
    ];
    $label = $labels[$status] ?? strtoupper($status);
    return '<span class="stamp stamp-' . h($status) . '">' . h($label) . '</span>';
}
