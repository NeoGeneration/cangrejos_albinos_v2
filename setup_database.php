<?php
// Configuración inicial de la conexión sin especificar base de datos
$db_host = '127.0.0.1'; // Usamos IP en lugar de localhost para evitar problemas de socket
$db_user = 'root'; // Cambia esto por tu usuario de MySQL
$db_pass = ''; // Cambia esto por tu contraseña de MySQL
$db_port = 3306; // Puerto por defecto de MySQL

// Mostrar información de configuración
echo "<strong>Configuración de conexión:</strong><br>";
echo "Host: $db_host<br>";
echo "Usuario: $db_user<br>";
echo "Puerto: $db_port<br><br>";

// Conectar sin especificar base de datos
try {
    $conn = mysqli_connect($db_host, $db_user, $db_pass, '', $db_port);
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; background-color: #ffeeee; border: 1px solid #ff0000; margin-bottom: 20px;'>";
    echo "<strong>Error de conexión:</strong> " . $e->getMessage() . "<br><br>";
    echo "<strong>Posibles soluciones:</strong><br>";
    echo "1. Asegúrate de que el servidor MySQL esté en ejecución.<br>";
    echo "2. Verifica tus credenciales de MySQL en este archivo (setup_database.php).<br>";
    echo "3. Si usas MAMP o XAMPP, es posible que debas ajustar el host o el puerto.<br>";
    echo "4. Para MySQL instalado con Homebrew, asegúrate de haber ejecutado 'brew services start mysql'.<br>";
    echo "</div>";
    die();
}

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

echo "Conexión establecida con éxito.<br>";

// Crear base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS cangrejos_albinos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sql)) {
    echo "Base de datos 'cangrejos_albinos' creada o ya existente.<br>";
} else {
    echo "Error al crear la base de datos: " . mysqli_error($conn) . "<br>";
    die();
}

// Seleccionar la base de datos
mysqli_select_db($conn, 'u534707074_cangrejosalbin');
echo "Base de datos 'cangrejos_albinos' seleccionada.<br>";

// Leer el archivo SQL
$sql_file = file_get_contents('database.sql');

// Dividir el archivo SQL en múltiples consultas
$queries = explode(';', $sql_file);

// Añadir algo de estilo para mensajes
echo '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .query { font-family: monospace; background-color: #f8f9fa; padding: 5px; border-radius: 3px; margin: 5px 0; }
</style>';

// Ejecutar cada consulta
$success = true;
$errors = [];
$warnings = [];
$successes = [];

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        try {
            if (!mysqli_query($conn, $query)) {
                // Verificar si es un error de duplicado (1062)
                if (mysqli_errno($conn) == 1062) {
                    $warnings[] = "Advertencia: " . mysqli_error($conn) . " - Este elemento ya existe. Esto es normal si has ejecutado el script anteriormente.";
                } else {
                    $errors[] = "Error " . mysqli_errno($conn) . ": " . mysqli_error($conn) . " <div class='query'>" . htmlspecialchars($query) . "</div>";
                    $success = false;
                }
            } else {
                // Solo mostramos mensajes para ciertas consultas importantes
                if (stripos($query, 'CREATE TABLE') !== false) {
                    $table_name = preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $query, $matches) ? $matches[1] : 'tabla';
                    $successes[] = "Tabla '$table_name' creada correctamente.";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Excepción al ejecutar consulta: " . $e->getMessage() . " <div class='query'>" . htmlspecialchars($query) . "</div>";
            $success = false;
        }
    }
}

// Mostrar mensajes de éxito
if (!empty($successes)) {
    echo "<div class='success'>";
    echo "<strong>Operaciones completadas con éxito:</strong><br>";
    echo "<ul>";
    foreach ($successes as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Mostrar advertencias
if (!empty($warnings)) {
    echo "<div class='warning'>";
    echo "<strong>Advertencias (no críticas):</strong><br>";
    echo "<ul>";
    foreach ($warnings as $warning) {
        echo "<li>$warning</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Mostrar errores
if (!empty($errors)) {
    echo "<div class='error'>";
    echo "<strong>Errores encontrados:</strong><br>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if ($success) {
    echo "Esquema de base de datos importado correctamente.<br>";
} else {
    echo "Hubo errores al importar el esquema. Verifica los mensajes anteriores.<br>";
}

// Verificar que las tablas se hayan creado
$tables = ['reservations', 'admin_users'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "Tabla '$table' creada correctamente.<br>";
    } else {
        echo "¡Alerta! La tabla '$table' no se encuentra en la base de datos.<br>";
    }
}

// Insertar usuario administrador si no existe
$check_admin = mysqli_query($conn, "SELECT 1 FROM admin_users WHERE username = 'admin'");
if (mysqli_num_rows($check_admin) == 0) {
    // El usuario no existe, podemos crearlo
    $admin_username = 'admin';
    $admin_password = '$2y$10$Xx9QX.Nc7r9QNB7eDr3zIe7L4vB5Nob69XnQJJD9Ttx7Ufw0Y9QKC'; // hash de 'change_me_immediately'
    $admin_email = 'admin@cangrejos-albinos.com';
    
    $insert_admin = mysqli_query($conn, "INSERT INTO admin_users (username, password, email) VALUES ('$admin_username', '$admin_password', '$admin_email')");
    
    if ($insert_admin) {
        echo "<div class='success'>Usuario administrador 'admin' creado correctamente. Contraseña: change_me_immediately</div>";
    } else {
        echo "<div class='error'>Error al crear el usuario administrador: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='warning'>El usuario administrador 'admin' ya existe. No se ha creado uno nuevo.</div>";
}

// Cerrar conexión
mysqli_close($conn);
echo "<div style='margin-top: 20px;'>";
echo "<p>Proceso completado. Ya puedes usar la base de datos para el sistema de reservas.</p>";
echo "<a href='test_system.php' style='display: inline-block; background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Volver al Sistema de Prueba</a>";
echo "</div>";
?>