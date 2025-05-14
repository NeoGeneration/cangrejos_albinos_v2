<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "<h2>Diagnóstico del entorno PHP en producción</h2>";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p><strong>✔ autoload.php cargado correctamente</strong></p>";
} catch (Throwable $e) {
    echo "<p style='color:red'><strong>✖ Error al cargar autoload.php:</strong> {$e->getMessage()}</p>";
    exit;
}

try {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $env = str_contains($host, 'cangrejosalbinos.com') ? 'production' : 'local';

    echo "<p>Detectado entorno: <strong>{$env}</strong></p>";

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
    $dotenv->safeLoad();

    echo "<p><strong>✔ Variables de entorno cargadas</strong></p>";
} catch (Throwable $e) {
    echo "<p style='color:red'><strong>✖ Error al cargar dotenv:</strong> {$e->getMessage()}</p>";
    exit;
}

// Mostrar valores clave esperados
$claves = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
echo "<h3>Variables de entorno detectadas:</h3><ul>";
foreach ($claves as $clave) {
    $valor = $_ENV[$clave] ?? null;
    $estado = $valor ? '✔' : '✖';
    $color = $valor ? 'green' : 'red';
    echo "<li><strong>{$clave}</strong>: <span style='color:{$color}'>{$estado} {$valor}</span></li>";
}
echo "</ul>";

// Comprobación de conexión a base de datos
echo "<h3>Probando conexión a la base de datos...</h3>";
$conn = @mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn) {
    echo "<p style='color:green'><strong>✔ Conexión a MySQL establecida correctamente</strong></p>";
    mysqli_close($conn);
} else {
    echo "<p style='color:red'><strong>✖ Error de conexión a MySQL:</strong> " . mysqli_connect_error() . "</p>";
}