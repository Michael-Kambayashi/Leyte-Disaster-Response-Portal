<?php
/** Local-only CLI helper: php tools/create_admin.php admin "Demo Administrator" */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Command-line use only.');
}
require __DIR__ . '/../leyte_portal/config.php';
$username = $argv[1] ?? '';
$fullName = $argv[2] ?? '';
$password = getenv('LEYTE_ADMIN_PASSWORD');
if (!$username || !$fullName || !$password || strlen($password) < 12) {
    fwrite(STDERR, "Usage: set LEYTE_ADMIN_PASSWORD (12+ chars), then php tools/create_admin.php <username> <full_name>\n");
    exit(1);
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';
$stmt = $conn->prepare('INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)');
try {
    $stmt->bind_param('ssss', $username, $hash, $fullName, $role);
    $stmt->execute();
    echo "Created administrator account: $username\n";
} catch (mysqli_sql_exception $e) {
    fwrite(STDERR, "Could not create administrator (username may already exist).\n");
    exit(1);
}
