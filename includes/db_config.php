<?php
// Database configuration
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
// Establecer zona horaria de MySQL para la sesión
$conn->query("SET time_zone = 'Europe/Madrid'");
?>