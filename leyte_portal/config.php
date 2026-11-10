<?php
// Load local (gitignored) settings or process environment variables.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$local = is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];
$host = getenv('LEYTE_DB_HOST') ?: ($local['host'] ?? '127.0.0.1');
$user = getenv('LEYTE_DB_USER') ?: ($local['username'] ?? '');
$pass = getenv('LEYTE_DB_PASSWORD');
$pass = ($pass !== false) ? $pass : ($local['password'] ?? '');
$dbname = getenv('LEYTE_DB_NAME') ?: ($local['database'] ?? 'leyte_portal');

if (!$user) {
    http_response_code(500);
    exit('Database username is not configured. See README.md.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to connect to the database. Check local configuration.');
}

// CSRF protection for state-changing POST requests.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        http_response_code(403);
        exit('Invalid form token. Reload the page and retry.');
    }
}
function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

// Escape untrusted content before rendering as HTML.
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
