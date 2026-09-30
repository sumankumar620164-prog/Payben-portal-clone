<?php
/**
 * Database connection.
 * Update these four values for your environment (local server, shared
 * hosting, etc). Nothing else in the app needs to change.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'payben_portal');
define('DB_USER', 'root');
define('DB_PASS', 'root');

/**
 * Auto-detect the app's base URL path so links, form actions, and
 * redirects work whether the project sits at the web root
 * (http://localhost/) or in a subfolder — which is how XAMPP's htdocs
 * works by default (http://localhost/payben-portal/).
 */
function detect_base_url(): string
{
    $app_root = realpath(__DIR__ . '/..');
    $doc_root = isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] !== ''
        ? realpath($_SERVER['DOCUMENT_ROOT'])
        : false;

    if (!$app_root || !$doc_root) {
        return '';
    }

    // Normalise Windows backslashes (XAMPP on Windows) to forward slashes.
    $app_root = str_replace('\\', '/', $app_root);
    $doc_root = str_replace('\\', '/', $doc_root);

    if (strpos($app_root, $doc_root) !== 0) {
        return '';
    }

    return rtrim(substr($app_root, strlen($doc_root)), '/');
}

define('BASE_URL', detect_base_url());

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Check config/database.php — ' . $e->getMessage());
        }
    }

    return $pdo;
}
