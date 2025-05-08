<?php
// Script para verificar la conexión a la base de datos y la configuración de PHP

echo '<h1>Verificación de conexión a base de datos</h1>';

// Verificar versión de PHP
echo '<h2>Información de PHP</h2>';
echo '<p><strong>Versión de PHP:</strong> ' . phpversion() . '</p>';
echo '<p><strong>Extensiones cargadas:</strong></p>';
echo '<ul>';
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo '<li>' . $ext . '</li>';
}
echo '</ul>';

// Verificar soporte para mysqli
if (extension_loaded('mysqli')) {
    echo '<p style="color:green">✅ La extensión mysqli está cargada.</p>';
} else {
    echo '<p style="color:red">❌ La extensión mysqli NO está cargada. Se requiere para conectar a MySQL.</p>';
}

// Verificar configuración de sesiones
echo '<h2>Configuración de sesiones</h2>';
echo '<p><strong>session.save_handler:</strong> ' . ini_get('session.save_handler') . '</p>';
echo '<p><strong>session.save_path:</strong> ' . ini_get('session.save_path') . '</p>';
echo '<p><strong>session.gc_maxlifetime:</strong> ' . ini_get('session.gc_maxlifetime') . ' segundos</p>';

// Intentar conectar a la base de datos usando los parámetros de conexión
echo '<h2>Prueba de conexión a la base de datos</h2>';

// Cargar los parámetros de conexión desde el archivo de configuración
require_once 'includes/db_config.php';

// Mostrar los parámetros de conexión (sin la contraseña)
echo '<p><strong>Host:</strong> ' . DB_HOST . '</p>';
echo '<p><strong>Usuario:</strong> ' . DB_USER . '</p>';
echo '<p><strong>Base de datos:</strong> ' . DB_NAME . '</p>';
echo '<p><strong>Puerto:</strong> ' . (defined('DB_PORT') ? DB_PORT : '3306 (predeterminado)') . '</p>';

// Verificar si la conexión existe
if (isset($conn) && $conn instanceof mysqli) {
    if ($conn->connect_error) {
        echo '<p style="color:red">❌ Error de conexión: ' . $conn->connect_error . '</p>';
    } else {
        echo '<p style="color:green">✅ Conexión establecida correctamente.</p>';
        
        // Obtener la versión de MySQL
        $version_result = $conn->query("SELECT VERSION() as version");
        $version_info = $version_result->fetch_assoc();
        echo '<p><strong>Versión de MySQL:</strong> ' . $version_info['version'] . '</p>';
        
        // Verificar que la base de datos existe
        $db_check = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        if ($db_check->num_rows > 0) {
            echo '<p style="color:green">✅ La base de datos <strong>' . DB_NAME . '</strong> existe.</p>';
            
            // Mostrar las tablas de la base de datos
            $tables_result = $conn->query("SHOW TABLES");
            if ($tables_result->num_rows > 0) {
                echo '<p><strong>Tablas encontradas:</strong></p>';
                echo '<ul>';
                while ($table = $tables_result->fetch_array()) {
                    echo '<li>' . $table[0] . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p style="color:orange">⚠️ No se encontraron tablas en la base de datos.</p>';
            }
        } else {
            echo '<p style="color:red">❌ La base de datos <strong>' . DB_NAME . '</strong> no existe.</p>';
        }
    }
} else {
    echo '<p style="color:red">❌ La variable de conexión $conn no está definida o no es una instancia de mysqli.</p>';
}

// Probar una conexión manual para verificar los parámetros
echo '<h2>Prueba de conexión manual</h2>';

try {
    $test_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', defined('DB_PORT') ? DB_PORT : 3306);
    
    if ($test_conn->connect_error) {
        echo '<p style="color:red">❌ Error de conexión manual: ' . $test_conn->connect_error . '</p>';
    } else {
        echo '<p style="color:green">✅ Conexión manual establecida correctamente (sin seleccionar base de datos).</p>';
        
        // Verificar si podemos crear la base de datos (si no existe)
        $create_result = $test_conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        if ($create_result) {
            echo '<p style="color:green">✅ Base de datos creada o ya existente.</p>';
            
            // Intentar seleccionar la base de datos
            if ($test_conn->select_db(DB_NAME)) {
                echo '<p style="color:green">✅ Se pudo seleccionar la base de datos ' . DB_NAME . '.</p>';
            } else {
                echo '<p style="color:red">❌ No se pudo seleccionar la base de datos: ' . $test_conn->error . '</p>';
            }
        } else {
            echo '<p style="color:red">❌ Error al crear la base de datos: ' . $test_conn->error . '</p>';
        }
        
        $test_conn->close();
    }
} catch (Exception $e) {
    echo '<p style="color:red">❌ Excepción al intentar la conexión manual: ' . $e->getMessage() . '</p>';
}

// Verificar el estado del servidor MySQL
echo '<h2>Estado del servidor MySQL</h2>';

$mysql_running = false;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Código para Windows
    exec('sc query mysql', $output, $return_var);
    
    echo '<pre>' . implode("\n", $output) . '</pre>';
    
    $mysql_running = strpos(implode("\n", $output), 'RUNNING') !== false;
} else {
    // Código para Unix/Linux/Mac
    exec('ps aux | grep mysql | grep -v grep', $output, $return_var);
    
    echo '<pre>' . implode("\n", $output) . '</pre>';
    
    $mysql_running = !empty($output);
}

if ($mysql_running) {
    echo '<p style="color:green">✅ El servidor MySQL parece estar en ejecución.</p>';
} else {
    echo '<p style="color:red">❌ El servidor MySQL no parece estar en ejecución o no se pudo detectar.</p>';
    
    // Para Mac con Homebrew
    echo '<p>Si estás usando Mac con Homebrew, intenta iniciar MySQL con:</p>';
    echo '<pre>brew services start mysql</pre>';
    
    // Para servicios de XAMPP/MAMP
    echo '<p>Si estás usando XAMPP o MAMP, asegúrate de que el servicio MySQL esté iniciado desde el panel de control.</p>';
}

// Enlaces útiles
echo '<div style="margin-top: 20px;">';
echo '<p><a href="admin/check_users.php">Verificar usuarios admin</a> | <a href="admin/debug_login.php">Depurar inicio de sesión</a> | <a href="test_system.php">Volver al sistema de prueba</a></p>';
echo '</div>';
?>