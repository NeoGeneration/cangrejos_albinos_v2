<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Redirect to admin dashboard
    header('Location: dashboard.php');
    exit;
}

// Include database configuration
require_once '../includes/db_config.php';

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Generate and check CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $login_err = "Error de seguridad. Por favor, intenta nuevamente.";
    } else {
        // Check if username is empty
        if (empty(trim($_POST["username"]))) {
            $username_err = "Por favor, introduce tu nombre de usuario.";
        } else {
            $username = trim($_POST["username"]);
        }
        
        // Check if password is empty
        if (empty(trim($_POST["password"]))) {
            $password_err = "Por favor, introduce tu contraseña.";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Validate credentials
        if (empty($username_err) && empty($password_err)) {
            // Prepare a select statement
            $sql = "SELECT id, username, password FROM admin_users WHERE username = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("s", $param_username);
                
                // Set parameters
                $param_username = $username;
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();
                    
                    // Check if username exists, if yes then verify password
                    if ($stmt->num_rows == 1) {                    
                        // Bind result variables
                        $stmt->bind_result($id, $username, $hashed_password);
                        if ($stmt->fetch()) {
                            if (password_verify($password, $hashed_password)) {
                                // Password is correct, start a new session
                                session_regenerate_id(true);
                                
                                // Store data in session variables
                                $_SESSION["admin_logged_in"] = true;
                                $_SESSION["admin_id"] = $id;
                                $_SESSION["admin_username"] = $username;
                                
                                // Debug log
                                error_log("Sesión establecida en login: admin_logged_in={$_SESSION["admin_logged_in"]}, admin_id={$_SESSION["admin_id"]}, admin_username={$_SESSION["admin_username"]}");
                                
                                // Update last login time
                                $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                                if ($update_stmt = $conn->prepare($update_sql)) {
                                    $update_stmt->bind_param("i", $id);
                                    $update_stmt->execute();
                                    $update_stmt->close();
                                }
                                
                                // Redirect user to dashboard
                                header("location: dashboard.php");
                                exit;
                            } else {
                                // Password is not valid
                                $login_err = "Usuario o contraseña incorrectos.";
                            }
                        }
                    } else {
                        // Username doesn't exist
                        $login_err = "Usuario o contraseña incorrectos.";
                    }
                } else {
                    $login_err = "¡Ups! Algo salió mal. Por favor, inténtalo más tarde.";
                }
                
                // Close statement
                $stmt->close();
            }
        }
    }
    
    // Close connection
    $conn->close();
}

// Generate new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Cangrejos Albinos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 400px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem;
        }
        .login-form {
            padding: 2rem;
        }
        .btn-login {
            font-size: 0.9rem;
            letter-spacing: 0.05rem;
            padding: 0.75rem 1rem;
            background-color: #007bff;
            border-color: #007bff;
        }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Panel de Administración</h3>
                <p class="mb-0">Cangrejos Albinos</p>
            </div>
            <div class="card-body login-form">
                <?php if (!empty($login_err)) : ?>
                    <div class="alert alert-danger"><?php echo $login_err; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-login btn-block">Iniciar Sesión</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>