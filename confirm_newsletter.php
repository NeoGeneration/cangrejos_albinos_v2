<?php
/**
 * Confirma una suscripción de newsletter
 */

// Incluir la configuración de la base de datos
require_once 'includes/db_config.php';

// Inicializar variables
$success = false;
$message = '';

// Obtener y validar token
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $message = 'Token de confirmación no válido o expirado.';
} else {
    try {
        // Conectar a la base de datos
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buscar suscripción por token
        $stmt = $conn->prepare("SELECT id, email, is_confirmed FROM newsletter_subscribers WHERE confirmation_token = ?");
        $stmt->execute([$token]);
        $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($subscriber) {
            if ($subscriber['is_confirmed']) {
                $message = 'Tu suscripción ya ha sido confirmada anteriormente.';
                $success = true;
            } else {
                // Confirmar suscripción
                $stmt = $conn->prepare("UPDATE newsletter_subscribers SET is_confirmed = 1, confirm_date = NOW() WHERE id = ?");
                $stmt->execute([$subscriber['id']]);
                
                $message = '¡Gracias! Tu suscripción a nuestro newsletter ha sido confirmada.';
                $success = true;
            }
        } else {
            $message = 'Token de confirmación no válido o expirado.';
        }
        
    } catch (PDOException $e) {
        error_log("Error en confirm_newsletter.php: " . $e->getMessage());
        $message = 'Lo sentimos, ha ocurrido un error. Por favor, inténtalo de nuevo más tarde.';
    }
}

// HTML para la página de confirmación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de suscripción | Cangrejos Albinos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .confirmation-box {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 60px;
            margin-bottom: 20px;
        }
        .error-icon {
            color: #dc3545;
            font-size: 60px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-box">
            <?php if ($success): ?>
                <div class="success-icon">✓</div>
                <h2>¡Suscripción Confirmada!</h2>
            <?php else: ?>
                <div class="error-icon">✗</div>
                <h2>Error de Confirmación</h2>
            <?php endif; ?>
            
            <p><?php echo $message; ?></p>
            
            <div class="mt-4">
                <a href="index.php" class="btn theme-btn">Volver a la página principal</a>
            </div>
        </div>
    </div>
</body>
</html>