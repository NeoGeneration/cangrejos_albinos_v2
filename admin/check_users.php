<?php
// Script para verificar si la tabla admin_users existe y si contiene el usuario admin

// Incluir configuración de base de datos
require_once '../includes/db_config.php';

echo '<h1>Verificación de usuarios administradores</h1>';

// Verificar si la tabla admin_users existe
$table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($table_check->num_rows > 0) {
    echo '<p style="color:green">✅ La tabla <strong>admin_users</strong> existe en la base de datos.</p>';
    
    // Verificar si hay usuarios en la tabla
    $users_query = $conn->query("SELECT id, username, email FROM admin_users");
    
    if ($users_query->num_rows > 0) {
        echo '<p>Usuarios encontrados en la tabla:</p>';
        echo '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
        echo '<tr><th>ID</th><th>Usuario</th><th>Email</th></tr>';
        
        while ($user = $users_query->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $user['id'] . '</td>';
            echo '<td>' . htmlspecialchars($user['username']) . '</td>';
            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p style="color:red">❌ No se encontraron usuarios en la tabla admin_users.</p>';
        
        // Crear usuario admin
        echo '<h2>Creando usuario administrador predeterminado</h2>';
        
        $username = 'admin';
        $password = password_hash('change_me_immediately', PASSWORD_DEFAULT);
        $email = 'admin@cangrejos-albinos.com';
        
        $insert_sql = "INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sss", $username, $password, $email);
        
        if ($stmt->execute()) {
            echo '<p style="color:green">✅ Usuario administrador creado con éxito.</p>';
            echo '<p>Usuario: <strong>admin</strong><br>Contraseña: <strong>change_me_immediately</strong></p>';
        } else {
            echo '<p style="color:red">❌ Error al crear el usuario administrador: ' . $stmt->error . '</p>';
        }
    }
} else {
    echo '<p style="color:red">❌ La tabla <strong>admin_users</strong> no existe en la base de datos.</p>';
    
    // Crear la tabla
    echo '<h2>Creando tabla admin_users</h2>';
    
    $create_table_sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (username),
        UNIQUE KEY (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo '<p style="color:green">✅ Tabla admin_users creada con éxito.</p>';
        
        // Crear usuario admin
        $username = 'admin';
        $password = password_hash('change_me_immediately', PASSWORD_DEFAULT);
        $email = 'admin@cangrejos-albinos.com';
        
        $insert_sql = "INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sss", $username, $password, $email);
        
        if ($stmt->execute()) {
            echo '<p style="color:green">✅ Usuario administrador creado con éxito.</p>';
            echo '<p>Usuario: <strong>admin</strong><br>Contraseña: <strong>change_me_immediately</strong></p>';
        } else {
            echo '<p style="color:red">❌ Error al crear el usuario administrador: ' . $stmt->error . '</p>';
        }
    } else {
        echo '<p style="color:red">❌ Error al crear la tabla: ' . $conn->error . '</p>';
    }
}

// Crear un formulario para agregar un usuario personalizado
echo '<h2>Crear un nuevo usuario administrador</h2>';
echo '<form method="post" action="">';
echo '<p><label>Usuario: <input type="text" name="new_username" required></label></p>';
echo '<p><label>Contraseña: <input type="password" name="new_password" required></label></p>';
echo '<p><label>Email: <input type="email" name="new_email" required></label></p>';
echo '<p><button type="submit" name="create_user">Crear Usuario</button></p>';
echo '</form>';

// Procesar el formulario si se envió
if (isset($_POST['create_user'])) {
    $new_username = trim($_POST['new_username']);
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
    $new_email = trim($_POST['new_email']);
    
    // Verificar si el usuario o email ya existen
    $check_sql = "SELECT * FROM admin_users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $new_username, $new_email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo '<p style="color:red">❌ Error: El usuario o email ya existen.</p>';
    } else {
        // Insertar el nuevo usuario
        $insert_sql = "INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $new_username, $new_password, $new_email);
        
        if ($insert_stmt->execute()) {
            echo '<p style="color:green">✅ Usuario ' . htmlspecialchars($new_username) . ' creado con éxito.</p>';
        } else {
            echo '<p style="color:red">❌ Error al crear el usuario: ' . $insert_stmt->error . '</p>';
        }
    }
}

// Enlaces de utilidad
echo '<div style="margin-top: 20px;">';
echo '<p><a href="index.php">Ir al panel de login</a> | <a href="../test_system.php">Volver al sistema de prueba</a></p>';
echo '</div>';

// Cerrar conexión
$conn->close();
?>