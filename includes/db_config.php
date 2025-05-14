<?php
// === BACKUP DEL CÓDIGO ORIGINAL ===
/*
define('DB_HOST', '127.0.0.1'); // Usamos IP en lugar de localhost para evitar problemas de socket
define('DB_USER', 'u534707074_cangrejos'); // Cambia esto a tu usuario de base de datos
define('DB_PASS', 'QfyR[y?5ru822'); // Cambia esto a tu contraseña de base de datos
define('DB_NAME', 'u534707074_cangrejosalbin');
define('DB_PORT', 3306); // Puerto predeterminado de MySQL

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to ensure proper encoding
mysqli_set_charset($conn, "utf8mb4");
*/

// === NUEVO CÓDIGO CON .env ===
require_once __DIR__ . '/../vendor/autoload.php';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (str_contains($host, 'cangrejosalbinos.com')) {
    $env = 'production';
} else {
    $env = 'local';
}
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', ".env.$env");
$dotenv->safeLoad();

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_PORT', 3306);

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>