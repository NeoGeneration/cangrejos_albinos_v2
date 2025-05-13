<?php
// Start session
session_start();

// Include database configuration and email template system
require_once 'includes/db_config.php';
require_once 'includes/email/email_template.php';

// Define variables
$token = '';
$error = '';
$success = false;
$reservation = null;

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Validate token format (alphanumeric, 32-64 chars)
    if (preg_match('/^[a-zA-Z0-9]{32,64}$/', $token)) {
        // Token looks valid, check if it exists in database
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE confirmation_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Token is valid and exists
            $reservation = $result->fetch_assoc();
            
            // Check if already confirmed
            if ($reservation['email_confirmed'] == 1) {
                $error = 'Esta reserva ya ha sido confirmada anteriormente.';
            } else {
                // Update reservation status - cambiamos directamente a 'confirmed'
                $update_stmt = $conn->prepare("UPDATE reservations SET email_confirmed = 1, status = 'confirmed', confirmation_token = NULL WHERE id = ?");
                $update_stmt->bind_param("i", $reservation['id']);
                
                if ($update_stmt->execute()) {
                    $success = true;
                    // Generate base URL
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                    $baseURL = $protocol . "://" . $host;
                    
                    // Prepare data for confirmation email to user
                    $user_data = [
                        'id' => $reservation['id'],
                        'name' => $reservation['name'],
                        'last_name' => $reservation['last_name'],
                        'email' => $reservation['email'],
                        'phone' => $reservation['phone'],
                        'confirmation_code' => $reservation['confirmation_code'],
                        'num_tickets' => $reservation['num_tickets'],
                        'reservation_date' => $reservation['reservation_date']
                    ];
                    
                    // Send confirmation email to user using template system
                    send_template_email($reservation['email'], EMAIL_TYPE_CONFIRMATION, $user_data, $baseURL);
                    
                    // Prepare data for admin notification
                    $admin_data = $user_data;
                    $admin_data['action_type'] = 'new_reservation';
                    
                    // Send notification to admin using template system
                    $admin_email = 'admin@cangrejos-albinos.com'; // Cambiar por el email real del administrador
                    send_template_email($admin_email, EMAIL_TYPE_ADMIN_NOTIFICATION, $admin_data, $baseURL);
                    
                    $update_stmt->close();
                } else {
                    $error = 'Error al confirmar la reserva. Por favor, inténtalo nuevamente o contacta con nosotros.';
                }
            }
        } else {
            $error = 'El código de confirmación no es válido o ya ha sido utilizado.';
        }
        
        $stmt->close();
    } else {
        $error = 'El formato del código de confirmación no es válido.';
    }
} else {
    $error = 'No se ha proporcionado un código de confirmación.';
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Reserva Confirmada' : 'Confirmar Reserva'; ?> | Cangrejos Albinos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/default.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .confirmation-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .success-icon {
            color: #28a745;
        }
        .error-icon {
            color: #dc3545;
        }
        .confirmation-message {
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }
        .confirmation-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            text-align: left;
            margin-bottom: 30px;
        }
        .confirmation-details h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .final {
              text-align: left;
        } 
        li {
            list-style: square;
            margin-left: 20px;
            padding-left: 10px;
            line-height: 1.6;
        }
</style>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-96MVM31JD0"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-96MVM31JD0');
</script>
</head>
<body>
    <div class="confirmation-container">
        <?php if ($success): ?>
            <div class="confirmation-icon success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>¡Reserva Confirmada!</h1>
            <div class="confirmation-message">
                <p>¡Tu reserva para el evento "Cangrejos Albinos" ha sido confirmada correctamente!</p>
                <p>Tu reserva está ahora <strong>confirmada</strong>. Ya tienes tus entradas aseguradas para este evento.</p>
            </div>
            
            <?php if ($reservation): ?>
            <div class="confirmation-details">
                <h3>Detalles de tu reserva:</h3>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($reservation['name'] . ' ' . $reservation['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($reservation['email']); ?></p>
                <p><strong>Número de entradas:</strong> <?php echo $reservation['num_tickets']; ?></p>
                <p><strong>Fecha del evento:</strong> 17 de mayo de 2025</p>
                <p><strong>Hora:</strong> 20:30</p>
                <p><strong>Lugar:</strong> Jameos del Agua, Lanzarote</p>
            </div>
            <?php endif; ?>
            
            <p>Te recomendamos que guardes o imprimas esta página como confirmación.</p>
            <div class="final">
                <p><strong>Información importante para su asistencia:</strong></p>
                <ul>
                    <li><strong>Llegue con anticipación</strong> para facilitar las tareas de acomodación. Tenga en cuenta que hay una caminata de varios minutos desde la entrada hasta el auditorio.</li>
                    <li><strong>Personas con movilidad reducida</strong>, póngase en contacto con atención al cliente (<a href="mailto:info@centrosturisticos.com">info@centrosturisticos.com</a>) antes del evento.</li>
                    <li>Utilice la puerta superior de Jameos para el <strong>acceso</strong>.</li>
                    <li>Asegúrese de llevar su invitación, ya sea <strong>impresa o digital</strong>.</li>
                    <li>Tenga en cuenta que no es posible visitar el resto de espacios; el acceso está permitido solo al auditorio.</li>
                    <li>Debido a la superficie irregular del suelo volcánico, le recomendamos que use calzado cómodo.</li>
                    <li>Si tiene alguna duda, póngase en contacto con nosotros a través de <a href="mailto:info@centrosturisticos.com">info@centrosturisticos.com</a></li>
                </ul>
            </div>
            <p>¡Gracias por tu reserva y esperamos verte pronto!</p>
            
            <a href="index.php#entradas" class="td-btn mt-3">Volver a la página principal</a>
            
        <?php else: ?>
            <div class="confirmation-icon error-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1>Error de Confirmación</h1>
            <div class="confirmation-message">s
                <p><?php echo $error; ?></p>
            </div>
            <p>Si tienes problemas para confirmar tu reserva, por favor contáctanos:</p>
            <p><strong>Email:</strong> info@cactlanzarote.com</p>
            <p><strong>Teléfono:</strong> +34 928 849 444</p>
            
            <a href="index.php#entradas" class="td-btn mt-3">Volver a la página principal</a>
        <?php endif; ?>
    </div>
    
    <!-- JS here -->
    <script src="assets/js/vendor/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/fontawesome-all.min.js"></script>
</body>
</html>