<?php
// Este script te permite actualizar la contraseña de MySQL en los archivos de configuración

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['db_password'])) {
    $new_password = $_POST['db_password'];
    $host = isset($_POST['db_host']) ? $_POST['db_host'] : '127.0.0.1';
    $user = isset($_POST['db_user']) ? $_POST['db_user'] : 'root';
    $port = isset($_POST['db_port']) ? (int)$_POST['db_port'] : 3306;
    
    // Actualizar archivo db_config.php
    $db_config_path = 'includes/db_config.php';
    if (file_exists($db_config_path)) {
        $db_config_content = file_get_contents($db_config_path);
        
        // Actualizar host
        $db_config_content = preg_replace(
            "/define\('DB_HOST',\s*'[^']*'\);/",
            "define('DB_HOST', '$host'); // Usamos IP en lugar de localhost para evitar problemas de socket",
            $db_config_content
        );
        
        // Actualizar usuario
        $db_config_content = preg_replace(
            "/define\('DB_USER',\s*'[^']*'\);/",
            "define('DB_USER', '$user'); // Usuario de MySQL",
            $db_config_content
        );
        
        // Actualizar contraseña
        $db_config_content = preg_replace(
            "/define\('DB_PASS',\s*'[^']*'\);/",
            "define('DB_PASS', '$new_password'); // Contraseña de MySQL",
            $db_config_content
        );
        
        // Actualizar puerto
        $db_config_content = preg_replace(
            "/define\('DB_PORT',\s*\d+\);/",
            "define('DB_PORT', $port); // Puerto de MySQL",
            $db_config_content
        );
        
        file_put_contents($db_config_path, $db_config_content);
    }
    
    // Actualizar setup_database.php
    $setup_db_path = 'setup_database.php';
    if (file_exists($setup_db_path)) {
        $setup_db_content = file_get_contents($setup_db_path);
        
        // Actualizar host
        $setup_db_content = preg_replace(
            "/\\\$db_host\s*=\s*'[^']*';/",
            "\$db_host = '$host'; // Host de MySQL",
            $setup_db_content
        );
        
        // Actualizar usuario
        $setup_db_content = preg_replace(
            "/\\\$db_user\s*=\s*'[^']*';/",
            "\$db_user = '$user'; // Usuario de MySQL",
            $setup_db_content
        );
        
        // Actualizar contraseña
        $setup_db_content = preg_replace(
            "/\\\$db_pass\s*=\s*'[^']*';/",
            "\$db_pass = '$new_password'; // Contraseña de MySQL",
            $setup_db_content
        );
        
        // Actualizar puerto
        $setup_db_content = preg_replace(
            "/\\\$db_port\s*=\s*\d+;/",
            "\$db_port = $port; // Puerto de MySQL",
            $setup_db_content
        );
        
        file_put_contents($setup_db_path, $setup_db_content);
    }
    
    // Mostrar mensaje de éxito
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Base de Datos - Cangrejos Albinos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .card {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Configuración de MySQL</h1>
    
    <?php if (isset($success)): ?>
    <div class="alert alert-success">
        <strong>¡Éxito!</strong> La configuración ha sido actualizada.
        <p>Ahora puedes intentar <a href="setup_database.php">configurar la base de datos</a>.</p>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            Actualizar configuración de MySQL
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label for="db_host">Host de MySQL:</label>
                    <input type="text" class="form-control" id="db_host" name="db_host" value="127.0.0.1">
                    <small class="form-text text-muted">Usa 127.0.0.1 en lugar de localhost para evitar problemas de socket en macOS.</small>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Usuario de MySQL:</label>
                    <input type="text" class="form-control" id="db_user" name="db_user" value="root">
                </div>
                
                <div class="form-group">
                    <label for="db_password">Contraseña de MySQL:</label>
                    <input type="password" class="form-control" id="db_password" name="db_password" placeholder="Introduce la contraseña de MySQL">
                </div>
                
                <div class="form-group">
                    <label for="db_port">Puerto de MySQL:</label>
                    <input type="number" class="form-control" id="db_port" name="db_port" value="3306">
                    <small class="form-text text-muted">El puerto predeterminado suele ser 3306. Para MAMP puede ser 8889.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Actualizar Configuración</button>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            Información
        </div>
        <div class="card-body">
            <p>Este script actualiza la configuración de MySQL en los archivos:</p>
            <ul>
                <li><code>includes/db_config.php</code></li>
                <li><code>setup_database.php</code></li>
            </ul>
            <p>Si estás utilizando MAMP:</p>
            <ul>
                <li>Usuario: <code>root</code></li>
                <li>Contraseña: <code>root</code> (por defecto)</li>
                <li>Puerto: <code>8889</code> (por defecto)</li>
            </ul>
            <p>Si estás utilizando MySQL instalado con Homebrew:</p>
            <ul>
                <li>Usuario: <code>root</code></li>
                <li>Contraseña: puede estar vacía o configurada durante la instalación</li>
                <li>Puerto: <code>3306</code> (por defecto)</li>
            </ul>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <a href="test_system.php" class="btn btn-secondary">Volver al Sistema de Prueba</a>
    </div>
</body>
</html>