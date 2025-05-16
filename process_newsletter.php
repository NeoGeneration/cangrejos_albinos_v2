<?php
/**
 * Procesa las solicitudes de suscripción al newsletter
 */

// Asegurar que se envían cabeceras de JSON
header('Content-Type: application/json');

// Control de errores PHP
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en la salida
ini_set('log_errors', 1); // Log de errores activado

// Capturar todas las salidas PHP
ob_start();

// Incluir la configuración de la base de datos
require_once 'includes/db_config.php';
require_once 'includes/email/email_template.php';

//define('DB_HOST', '127.0.0.1'); // Usamos IP en lugar de localhost para evitar problemas de socket
//define('DB_USER', 'u534707074_cangrejos'); // Cambia esto a tu usuario de base de datos
//define('DB_PASS', 'QfyR[y?5ru822'); // Cambia esto a tu contraseña de base de datos
//define('DB_NAME', 'u534707074_cangrejosalbin');
//define('DB_PORT', 3306); // Puerto predeterminado de MySQL

$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;
$db_port = DB_PORT; 

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
    // Convertir variables a valores directos para depuración
    $conn_string = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $response['debug_connection'] = "Intentando conectar a: $db_host / $db_name";
    
    // Conectar a la base de datos
    $conn = new PDO($conn_string, $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar estructura de la tabla (para depuración)
    try {
        $table_check = $conn->query("SHOW TABLES LIKE 'newsletter_subscribers'");
        $table_exists = $table_check->rowCount() > 0;
        $response['debug_table_exists'] = $table_exists;
        
        if (!$table_exists) {
            // Si la tabla no existe, intenta crearla
            $conn->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                confirmation_token VARCHAR(255) NOT NULL,
                is_confirmed TINYINT(1) NOT NULL DEFAULT 0,
                subscribe_date DATETIME NOT NULL,
                confirm_date DATETIME NULL,
                ip_address VARCHAR(45) NULL,
                UNIQUE KEY (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $response['debug_table_created'] = true;
        }
    } catch (Exception $e) {
        $response['debug_table_check_error'] = $e->getMessage();
    }
    
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

// Capturar cualquier salida no deseada
$output = ob_get_clean();
if (!empty($output)) {
    // Si hubo alguna salida no deseada (errores, advertencias, etc.)
    error_log("Salida no deseada en process_newsletter.php: " . $output);
    $response['debug_output'] = $output; // Esto es útil para depuración
}

// Devolver respuesta como JSON limpio
echo json_encode($response);
?>