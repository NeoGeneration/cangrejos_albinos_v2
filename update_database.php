<?php
// Script para actualizar la estructura de la base de datos

// Incluir configuración de base de datos
require_once 'includes/db_config.php';

echo '<h1>Actualización de la estructura de la base de datos</h1>';

// Determinar qué archivo SQL usar (archivo normal o archivo directo)
$use_direct_approach = isset($_GET['direct']) && $_GET['direct'] == 1;
$sql_filename = $use_direct_approach ? 'database_update_direct.sql' : 'database_update.sql';

echo '<p>Usando archivo: <strong>' . $sql_filename . '</strong></p>';

// Leer el archivo SQL de actualización
$sql_file = file_get_contents($sql_filename);

// Dividir en consultas individuales
$queries = explode(';', $sql_file);

$success = true;
$results = [];

// Ejecutar cada consulta
foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;
    
    try {
        if ($use_direct_approach) {
            // En el enfoque directo, intentamos ejecutar cada alteración por separado
            // e ignoramos errores específicos que indican que la columna/índice ya existe
            $result = $conn->query($query);
            $error_code = $conn->errno;
            
            // Códigos de error que podemos ignorar:
            // 1060: Duplicate column name
            // 1061: Duplicate key name
            // 1091: Can't DROP, check that column/key exists
            $ignorable_errors = [1060, 1061, 1091];
            
            if ($result) {
                $results[] = [
                    'status' => 'success',
                    'message' => 'Consulta ejecutada correctamente: ' . substr($query, 0, 80) . '...'
                ];
            } else {
                if (in_array($error_code, $ignorable_errors)) {
                    // Error que podemos ignorar
                    $results[] = [
                        'status' => 'warning',
                        'message' => 'Advertencia: ' . $conn->error . ' - Esto es normal si la estructura ya existía.',
                        'query' => $query
                    ];
                } else {
                    $results[] = [
                        'status' => 'error',
                        'message' => 'Error ' . $error_code . ': ' . $conn->error,
                        'query' => $query
                    ];
                    $success = false;
                }
            }
        } else {
            // Enfoque normal
            if ($conn->query($query)) {
                $results[] = [
                    'status' => 'success',
                    'message' => 'Consulta ejecutada correctamente: ' . substr($query, 0, 80) . '...'
                ];
            } else {
                $results[] = [
                    'status' => 'error',
                    'message' => 'Error al ejecutar consulta: ' . $conn->error,
                    'query' => $query
                ];
                $success = false;
            }
        }
    } catch (Exception $e) {
        $results[] = [
            'status' => 'error',
            'message' => 'Excepción: ' . $e->getMessage(),
            'query' => $query
        ];
        $success = false;
    }
}

// Mostrar resultados con formato
echo '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: #ff8c00; }
    .query { font-family: monospace; background-color: #f5f5f5; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .result-list { list-style-type: none; padding: 0; }
    .result-item { margin-bottom: 10px; padding: 10px; border-radius: 5px; }
    .success-item { background-color: #f0fff0; border: 1px solid #d0e9c6; }
    .warning-item { background-color: #fff8e6; border: 1px solid #ffe0a6; }
    .error-item { background-color: #fff0f0; border: 1px solid #ebcccc; }
</style>';

echo '<h2>Resultados de la actualización:</h2>';
echo '<ul class="result-list">';
foreach ($results as $result) {
    switch ($result['status']) {
        case 'success':
            $class = 'success-item';
            $icon = '✅';
            break;
        case 'warning':
            $class = 'warning-item';
            $icon = '⚠️';
            break;
        default:
            $class = 'error-item';
            $icon = '❌';
    }
    
    echo '<li class="result-item ' . $class . '">';
    echo '<div><strong>' . $icon . ' ' . $result['message'] . '</strong></div>';
    
    if (isset($result['query'])) {
        echo '<div class="query">' . htmlspecialchars($result['query']) . '</div>';
    }
    
    echo '</li>';
}
echo '</ul>';

// Mostrar resumen
if ($success) {
    echo '<div class="success"><h3>✅ Actualización completada con éxito</h3></div>';
} else {
    echo '<div class="error"><h3>❌ Ocurrieron errores durante la actualización</h3></div>';
}

// Enlaces útiles
echo '<div style="margin-top: 20px;">';

// Mostrar enlaces específicos según el método usado
if ($use_direct_approach) {
    echo '<p><a href="update_database.php">Intentar con método alternativo</a> | ';
} else {
    echo '<p><a href="update_database.php?direct=1">Intentar con método directo</a> | ';
}

echo '<a href="check_connection.php">Verificar conexión</a> | <a href="test_system.php">Volver al sistema de prueba</a></p>';

// Ofrecer enfoque manual si ambos métodos fallan
if (!$success) {
    echo '<hr>';
    echo '<h3>Actualización manual</h3>';
    echo '<p>Si ambos métodos fallan, puede intentar ejecutar manualmente las siguientes consultas en su gestor de base de datos:</p>';
    echo '<pre style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto;">';
    echo "-- Agregar columna para token de confirmación\n";
    echo "ALTER TABLE reservations ADD COLUMN confirmation_token VARCHAR(64) DEFAULT NULL;\n\n";
    echo "-- Agregar columna para indicar si el email está confirmado\n";
    echo "ALTER TABLE reservations ADD COLUMN email_confirmed BOOLEAN DEFAULT 0;\n\n";
    echo "-- Agregar índice para búsquedas rápidas\n";
    echo "ALTER TABLE reservations ADD INDEX idx_confirmation_token (confirmation_token);\n\n";
    echo "-- Modificar el tipo de la columna status\n";
    echo "ALTER TABLE reservations MODIFY COLUMN status ENUM('confirmed', 'cancelled', 'email_pending') DEFAULT 'email_pending';\n";
    echo '</pre>';
}

echo '</div>';

// Cerrar conexión
$conn->close();
?>