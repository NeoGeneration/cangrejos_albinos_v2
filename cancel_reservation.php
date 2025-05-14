<?php
// Start session
session_start();

// Include database configuration and email template system
require_once 'includes/db_config.php';
require_once 'includes/email/email_template.php';

// Define variables
$code = '';
$email = '';
$error = '';
$success = false;
$reservation = null;

// Check if parameters are provided
if (isset($_GET['code']) && !empty($_GET['code']) && isset($_GET['email']) && !empty($_GET['email'])) {
    $code = trim($_GET['code']);
    $email = trim($_GET['email']);
    
    // Validate code format (alphanumeric, 32 chars)
    if (preg_match('/^[a-zA-Z0-9]{32}$/', $code) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Parameters look valid, check if reservation exists
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE confirmation_code = ? AND email = ?");
        $stmt->bind_param("ss", $code, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Reservation is valid
            $reservation = $result->fetch_assoc();
            
            // Check if already cancelled
            if ($reservation['status'] === 'cancelled') {
                $error = 'Esta reserva ya fue cancelada anteriormente.';
            } else {
                // Process cancellation if form is submitted with confirmation
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
                    // Update reservation status
                    $update_stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
                    $update_stmt->bind_param("i", $reservation['id']);
                    
                    if ($update_stmt->execute()) {
                        $success = true;
                        
                        // Actualizar el contador de entradas disponibles se hace automáticamente ahora
                        // gracias a la consulta SQL en process_reservation.php que excluye reservas canceladas
                        
                        // Generate base URL
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                        $baseURL = $protocol . "://" . $host;
                        
                        // Prepare data for cancellation email to user
                        $cancel_data = [
                            'id' => $reservation['id'],
                            'name' => $reservation['name'],
                            'last_name' => $reservation['last_name'],
                            'email' => $reservation['email'],
                            'phone' => $reservation['phone'],
                            'dni' => $reservation['dni'],
                            'confirmation_code' => $reservation['confirmation_code'],
                            'num_tickets' => $reservation['num_tickets'],
                            'reservation_date' => $reservation['reservation_date']
                        ];
                        
                        // Send cancellation email to user using template system
                        send_template_email($reservation['email'], EMAIL_TYPE_CANCELLATION, $cancel_data, $baseURL);
                        
                        // Prepare data for admin notification
                        $admin_data = $cancel_data;
                        $admin_data['action_type'] = 'cancellation';
                        
                        // Send notification to admin using template system
                        $admin_email = 'admin@cangrejos-albinos.com'; // Cambiar por el email real del administrador
                        send_template_email($admin_email, EMAIL_TYPE_ADMIN_NOTIFICATION, $admin_data, $baseURL);
                        
                        // Log the cancellation
                        error_log("Reserva #{$reservation['id']} cancelada por el usuario {$reservation['email']}. {$reservation['num_tickets']} entradas liberadas.");
                        
                        $update_stmt->close();
                    } else {
                        $error = 'Error al cancelar la reserva. Por favor, inténtalo nuevamente o contacta con nosotros.';
                    }
                }
            }
        } else {
            $error = 'No se encontró una reserva con los datos proporcionados.';
        }
        
        $stmt->close();
    } else {
        $error = 'Los parámetros proporcionados no son válidos.';
    }
} else {
    $error = 'Faltan datos necesarios para procesar la cancelación.';
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Reserva Cancelada' : 'Cancelar Reserva'; ?> | Cangrejos Albinos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/default.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.min.css">
    <style>
        .cancellation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .cancellation-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .success-icon {
            color: #28a745;
        }
        .error-icon {
            color: #dc3545;
        }
        .warning-icon {
            color: #ffc107;
        }
        .cancellation-message {
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }
        .cancellation-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            text-align: left;
            margin-bottom: 30px;
        }
        .cancellation-details h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .cancel-btn {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            padding: 10px 20px;
            font-weight: 600;
        }
        .cancel-btn:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
</head>
<body>
    <div class="cancellation-container">
        <?php if ($success): ?>
            <div class="cancellation-icon success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>¡Reserva Cancelada!</h1>
            <div class="cancellation-message">
                <p>Tu reserva para el evento "Cangrejos Albinos" ha sido cancelada correctamente.</p>
                <p>Hemos enviado un correo electrónico de confirmación a <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
            </div>
            
            <?php if ($reservation): ?>
            <div class="cancellation-details">
                <h3>Detalles de la reserva cancelada:</h3>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($reservation['name'] . ' ' . $reservation['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($reservation['email']); ?></p>
                <p><strong>Número de entradas:</strong> <?php echo $reservation['num_tickets']; ?></p>
                <p><strong>Fecha de cancelación:</strong> <?php echo date('d/m/Y H:i'); ?></p>
            </div>
            <?php endif; ?>
            
            <p>Gracias por informarnos con anticipación. <strong><?php echo $reservation['num_tickets'] > 1 ? "Las " . $reservation['num_tickets'] . " entradas han" : "La entrada ha"; ?></strong> sido liberada<?php echo $reservation['num_tickets'] > 1 ? "s" : ""; ?> para que otros asistentes puedan disfrutar del evento.</p>
            <p>Si has cancelado por error, puedes realizar una nueva reserva en nuestra página web.</p>
            
            <a href="index.php#entradas" class="td-btn mt-3">Volver a la página principal</a>
            
        <?php elseif (!empty($error)): ?>
            <!-- Mensaje de error -->
            <div class="cancellation-icon error-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1>Error de Cancelación</h1>
            <div class="cancellation-message">
                <p><?php echo $error; ?></p>
            </div>
            <p>Si tienes problemas para cancelar tu reserva, por favor contáctanos:</p>
            <p><strong>Email:</strong> info@cactlanzarote.com</p>
            
            <a href="index.php#entradas" class="td-btn mt-3">Volver a la página principal</a>
        <?php else: ?>
            <!-- Formulario de confirmación de cancelación -->
            <?php if ($reservation): ?>
                <div class="cancellation-icon warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1>Cancelar Reserva</h1>
                <div class="cancellation-message">
                    <p>Estás a punto de cancelar tu reserva para el evento "Cangrejos Albinos".</p>
                    <p><strong>Esta acción no se puede deshacer.</strong></p>
                </div>
                
                <div class="cancellation-details">
                    <h3>Detalles de la reserva:</h3>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($reservation['name'] . ' ' . $reservation['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($reservation['email']); ?></p>
                    <p><strong>Número de entradas:</strong> <?php echo $reservation['num_tickets']; ?></p>
                    <p><strong>Fecha del evento:</strong> 17 de mayo de 2025</p>
                    <p><strong>Hora:</strong> 20:30</p>
                    <p><strong>Lugar:</strong> Jameos del Agua, Lanzarote</p>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <p>¿Estás seguro de que deseas cancelar esta reserva?</p>
                    <div class="mt-4">
                        <a href="index.php#entradas" class="td-btn">No, mantener mi reserva</a>
                        <button type="submit" name="confirm_cancel" class="td-btn cancel-btn ml-2">Sí, cancelar reserva</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- No hay datos de reserva pero tampoco hay error explícito -->
                <div class="cancellation-icon error-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h1>Información no disponible</h1>
                <div class="cancellation-message">
                    <p>No se encontró información sobre la reserva.</p>
                </div>
                <a href="index.php#entradas" class="td-btn mt-3">Volver a la página principal</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- JS here -->
    <script src="assets/js/vendor/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>