<?php
// Mostrar errores (para depurar)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establecer timezone en PHP
date_default_timezone_set('Europe/Madrid');

// Datos de conexión (copia estos desde db_config.php si es necesario)
$host = '127.0.0.1';
$user = 'u534707074_cangrejos';
$password = 'QfyR[y?5ru822';
$database = 'u534707074_cangrejosalbin';
$port = 3306;

// Mostrar zona horaria PHP
echo "<h2>PHP</h2>";
echo "Zona horaria actual (PHP): " . date_default_timezone_get() . "<br>";
echo "Fecha/Hora actual (PHP): " . date('Y-m-d H:i:s') . "<br><br>";

// Conectar con MySQL
$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_error) {
    die("Error de conexión a MySQL: " . $conn->connect_error);
}

// Establecer zona horaria en MySQL (solo para esta conexión)
if (!$conn->query("SET time_zone = 'Europe/Madrid'")) {
    die("Error al establecer el timezone en MySQL: " . $conn->error);
}

// Obtener zona horaria de sesión MySQL
$res = $conn->query("SELECT @@session.time_zone AS timezone, NOW() AS now");
$row = $res->fetch_assoc();

echo "<h2>MySQL</h2>";
echo "Zona horaria de sesión (MySQL): " . $row['timezone'] . "<br>";
echo "Fecha/Hora actual (MySQL): " . $row['now'] . "<br>";

$conn->close();
?>