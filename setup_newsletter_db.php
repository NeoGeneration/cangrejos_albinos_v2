<?php
/**
 * Script para configurar las tablas de newsletter en la base de datos
 */

// Incluir la configuración de la base de datos
require_once 'includes/db_config.php';

// Cargar el SQL desde el archivo
$sql_content = file_get_contents('database_newsletter.sql');

if (!$sql_content) {
    die("Error: No se pudo leer el archivo database_newsletter.sql");
}

// Conectar a la base de datos
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Configuración de tablas de newsletter</h1>";
    
    // Dividir las consultas SQL
    $queries = explode(';', $sql_content);
    
    // Ejecutar cada consulta
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $conn->exec($query);
            echo "<p style='color: green;'>✓ Consulta ejecutada con éxito: " . htmlspecialchars(substr($query, 0, 100)) . "...</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error al ejecutar la consulta: " . htmlspecialchars(substr($query, 0, 100)) . "...</p>";
            echo "<p style='color: red;'>Mensaje de error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<p style='font-weight: bold; margin-top: 20px;'>Configuración de base de datos para newsletter completada.</p>";
    echo "<p><a href='index.php'>Volver a la página principal</a></p>";
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>