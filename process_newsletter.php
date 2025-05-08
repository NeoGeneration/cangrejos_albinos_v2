<?php
/**
 * Procesa las solicitudes de suscripción al newsletter
 */

// Incluir la configuración de la base de datos
require_once 'includes/db_config.php';
require_once 'includes/email/email_template.php';

// Definir constantes para tipos de newsletter
define('EMAIL_TYPE_NEWSLETTER_CONFIRMATION', 'newsletter_confirmation');

// Inicializar respuesta
$response = array(
    'success' => false,
    'message' => '',
);

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido';
    echo json_encode($response);
    exit;
}

// Obtener y validar email
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validar email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Por favor, introduce un email válido';
    echo json_encode($response);
    exit;
}

try {
    // Conectar a la base de datos
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si el email ya existe y está confirmado
    $stmt = $conn->prepare("SELECT id, is_confirmed FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($subscriber && $subscriber['is_confirmed']) {
        $response['message'] = 'Este email ya está suscrito a nuestro newsletter';
        echo json_encode($response);
        exit;
    }
    
    // Generar token único para confirmación
    $confirmation_token = md5($email . time() . uniqid());
    
    // Obtener dirección IP del usuario
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    if ($subscriber) {
        // Actualizar el token existente
        $stmt = $conn->prepare("UPDATE newsletter_subscribers SET confirmation_token = ?, subscribe_date = NOW(), ip_address = ? WHERE id = ?");
        $stmt->execute([$confirmation_token, $ip_address, $subscriber['id']]);
    } else {
        // Insertar nuevo suscriptor
        $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, confirmation_token, subscribe_date, ip_address) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$email, $confirmation_token, $ip_address]);
    }
    
    // Enviar email de confirmación
    $baseURL = '';
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $baseURL = $protocol . "://" . $host;
    
    // Datos para el email
    $email_data = array(
        'email' => $email,
        'confirmation_token' => $confirmation_token,
        // Marcar este email como de newsletter
        'is_newsletter' => true,
        // Estos campos son necesarios para la plantilla de verificación
        'name' => 'Suscriptor',
        'last_name' => 'Newsletter',
        'confirmation_code' => substr($confirmation_token, 0, 8),
        'num_tickets' => 1
    );
    
    // La función de plantilla puede no tener el tipo EMAIL_TYPE_NEWSLETTER_CONFIRMATION implementado
    // Usamos el tipo VERIFICATION como alternativa temporal
    $email_type = defined('EMAIL_TYPE_NEWSLETTER_CONFIRMATION') ? EMAIL_TYPE_NEWSLETTER_CONFIRMATION : EMAIL_TYPE_VERIFICATION;
    
    if (send_template_email($email, $email_type, $email_data, $baseURL)) {
        $response['success'] = true;
        $response['message'] = 'Gracias por suscribirte. Te hemos enviado un email de confirmación.';
    } else {
        $response['message'] = 'Tu suscripción ha sido registrada, pero hubo un problema al enviar el email de confirmación.';
    }
    
} catch (PDOException $e) {
    error_log("Error en process_newsletter.php: " . $e->getMessage());
    // Mostrar el error para depuración
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
}

// Devolver respuesta como JSON
echo json_encode($response);
?>