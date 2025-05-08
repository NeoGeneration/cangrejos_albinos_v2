<?php
// Este script nos permite verificar si el mecanismo de login funciona correctamente

// Incluir configuración de base de datos
require_once '../includes/db_config.php';

echo '<h1>Depuración de inicio de sesión</h1>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['debug_login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    echo '<h2>Verificando credenciales</h2>';
    echo '<p>Usuario: <strong>' . htmlspecialchars($username) . '</strong></p>';
    
    // Buscar el usuario en la base de datos
    $sql = "SELECT id, username, password FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        echo '<p style="color:green">✅ Usuario encontrado en la base de datos.</p>';
        
        $user = $result->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            echo '<p style="color:green">✅ Contraseña correcta.</p>';
            echo '<p>¡Inicio de sesión exitoso!</p>';
            
            // Mostrar el hash almacenado para referencia
            echo '<p>Hash almacenado en la base de datos: ' . $user['password'] . '</p>';
        } else {
            echo '<p style="color:red">❌ Contraseña incorrecta.</p>';
            
            // Mostrar el hash almacenado para depuración
            echo '<p>Hash almacenado en la base de datos: ' . $user['password'] . '</p>';
            echo '<p>La contraseña que ingresaste no coincide con el hash almacenado.</p>';
            
            // Generar un nuevo hash con la contraseña proporcionada (para comparación)
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            echo '<p>Hash generado con la contraseña proporcionada: ' . $new_hash . '</p>';
        }
    } else {
        echo '<p style="color:red">❌ Usuario no encontrado en la base de datos.</p>';
        
        // Mostrar todos los usuarios disponibles
        $all_users = $conn->query("SELECT username FROM admin_users");
        if ($all_users->num_rows > 0) {
            echo '<p>Usuarios disponibles en la base de datos:</p>';
            echo '<ul>';
            while ($user = $all_users->fetch_assoc()) {
                echo '<li>' . htmlspecialchars($user['username']) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No hay usuarios en la base de datos.</p>';
        }
    }
}

// Formulario para probar el login
echo '<h2>Probar inicio de sesión</h2>';
echo '<form method="post" action="">';
echo '<p><label>Usuario: <input type="text" name="username" required value="admin"></label></p>';
echo '<p><label>Contraseña: <input type="password" name="password" required value="change_me_immediately"></label></p>';
echo '<p><button type="submit" name="debug_login">Verificar Credenciales</button></p>';
echo '</form>';

// Herramienta para actualizar la contraseña de un usuario existente
echo '<h2>Actualizar contraseña de usuario</h2>';
echo '<form method="post" action="">';
echo '<p><label>Usuario: <input type="text" name="update_username" required></label></p>';
echo '<p><label>Nueva contraseña: <input type="password" name="new_password" required></label></p>';
echo '<p><button type="submit" name="update_password">Actualizar Contraseña</button></p>';
echo '</form>';

// Procesar actualización de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $update_username = trim($_POST['update_username']);
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
    
    // Verificar si el usuario existe
    $check_sql = "SELECT * FROM admin_users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $update_username);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        // Actualizar la contraseña
        $update_sql = "UPDATE admin_users SET password = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $new_password, $update_username);
        
        if ($update_stmt->execute()) {
            echo '<p style="color:green">✅ Contraseña actualizada con éxito para el usuario ' . htmlspecialchars($update_username) . '.</p>';
        } else {
            echo '<p style="color:red">❌ Error al actualizar la contraseña: ' . $update_stmt->error . '</p>';
        }
    } else {
        echo '<p style="color:red">❌ El usuario ' . htmlspecialchars($update_username) . ' no existe.</p>';
    }
}

// Enlaces de utilidad
echo '<div style="margin-top: 20px;">';
echo '<p><a href="check_users.php">Verificar usuarios</a> | <a href="index.php">Ir al panel de login</a> | <a href="../test_system.php">Volver al sistema de prueba</a></p>';
echo '</div>';

// Cerrar conexión
$conn->close();
?>