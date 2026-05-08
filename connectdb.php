<?php
// Optional local configuration. Copy dbconfig.sample.php to dbconfig.php and update values.
$configFile = __DIR__ . '/dbconfig.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

function envOrDefault($name, $default)
{
    $value = getenv($name);
    return ($value !== false) ? $value : $default;
}

$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$user = defined('DB_USER') ? DB_USER : 'root';
$password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
$database = defined('DB_NAME') ? DB_NAME : 'gym_db';

$host = envOrDefault('DB_HOST', $host);
$user = envOrDefault('DB_USER', $user);
$password = envOrDefault('DB_PASSWORD', $password);
$database = envOrDefault('DB_NAME', $database);

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
?>