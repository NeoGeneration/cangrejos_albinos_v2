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

// Obtener zona horaria y hora actual de MySQL sin cambiar nada
$res = $conn->query("SELECT @@global.time_zone AS global_tz, @@session.time_zone AS session_tz, NOW() AS `mysql_now`");
if ($res) {
    $row = $res->fetch_assoc();
    echo "<h2>MySQL</h2>";
    echo "Zona horaria GLOBAL (MySQL): " . $row['global_tz'] . "<br>";
    echo "Zona horaria de SESIÓN (MySQL): " . $row['session_tz'] . "<br>";
echo "Fecha/Hora actual (MySQL): " . $row['mysql_now'] . "<br>";
} else {
    echo "Error al consultar el timezone de MySQL: " . $conn->error;
}

$conn->close();
?>