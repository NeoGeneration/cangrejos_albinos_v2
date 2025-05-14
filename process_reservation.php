<?php
// Start session for CSRF protection
session_start();

// Include database configuration and email template system
require_once 'includes/db_config.php';
require_once 'includes/email/email_template.php';

// Set content type to JSON for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
} else {
    // Regular form submission, will use redirects
}

// CSRF protection check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida. Por favor, intenta nuevamente.']);
    exit;
}

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Define constants
define('MAX_TICKETS_PER_PERSON', 4);

// Function to validate and sanitize input
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate and sanitize input fields
$name = isset($_POST['name']) ? validate_input($_POST['name']) : '';
$last_name = isset($_POST['last_name']) ? validate_input($_POST['last_name']) : '';
$email = isset($_POST['email']) ? validate_input($_POST['email']) : '';
$phone = isset($_POST['phone']) ? validate_input($_POST['phone']) : '';

$num_tickets = isset($_POST['num_tickets']) ? intval($_POST['num_tickets']) : 1;
$comments = isset($_POST['comments']) ? validate_input($_POST['comments']) : '';
$privacy_policy = isset($_POST['privacy_policy']) ? 1 : 0;

// Get IP address
$ip_address = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// Prepare error array
$errors = [];

// Validate name
if (empty($name)) {
    $errors[] = 'El nombre es obligatorio.';
} elseif (strlen($name) < 2) {
    $errors[] = 'El nombre debe tener al menos 2 caracteres.';
}

// Validate last name
if (empty($last_name)) {
    $errors[] = 'Los apellidos son obligatorios.';
} elseif (strlen($last_name) < 2) {
    $errors[] = 'Los apellidos deben tener al menos 2 caracteres.';
}

// Validate email
if (empty($email)) {
    $errors[] = 'El email es obligatorio.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Por favor, proporciona un email válido.';
}

// Validate phone
if (empty($phone)) {
    $errors[] = 'El teléfono es obligatorio.';
} elseif (!preg_match('/^[0-9]{9,}$/', $phone)) {
    $errors[] = 'Por favor, proporciona un teléfono válido (mínimo 9 dígitos).';
}



// Validate num_tickets
if ($num_tickets < 1 || $num_tickets > MAX_TICKETS_PER_PERSON) {
    $errors[] = 'El número de entradas debe estar entre 1 y ' . MAX_TICKETS_PER_PERSON . '.';
}

// Validate privacy policy
if (!$privacy_policy) {
    $errors[] = 'Debes aceptar la política de privacidad.';
}

// If there are validation errors, return them
if (!empty($errors)) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX response
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Por favor, corrige los siguientes errores:', 'errors' => $errors]);
    } else {
        // Regular form submission - redirect with errors
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Save form data to refill the form
        header('Location: index.php?error=validation#entradas');
    }
    exit;
}

// Check if tickets are still available
$stmt = $conn->prepare("SELECT SUM(num_tickets) as total_reserved FROM reservations WHERE status != 'cancelled'");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_reserved = $row['total_reserved'] ?: 0;

$total_tickets = 450; // You should define this value based on your event's capacity
$tickets_left = $total_tickets - $total_reserved;

if ($num_tickets > $tickets_left) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX response
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Lo sentimos, solo quedan ' . $tickets_left . ' entradas disponibles.'
        ]);
    } else {
        // Regular form submission - redirect with error
        $_SESSION['form_data'] = $_POST; // Save form data to refill the form
        header('Location: index.php?error=tickets&tickets_left=' . $tickets_left . '#entradas');
    }
    exit;
}

// Check if email already exists (limit one reservation per person)
$stmt = $conn->prepare("SELECT * FROM reservations WHERE email = ? AND status != 'cancelled'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX response
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Ya existe una reserva con este email. Por favor, contacta con nosotros si necesitas modificar tu reserva.'
        ]);
    } else {
        // Regular form submission - redirect with error
        header('Location: index.php?error=duplicate&msg=' . urlencode('Ya existe una reserva con este email. Por favor, contacta con nosotros si necesitas modificar tu reserva.') . '#entradas');
    }
    exit;
}

// Generate unique confirmation code and confirmation token
$confirmation_code = md5(uniqid(rand(), true));
$confirmation_token = md5(uniqid(rand(), true)) . bin2hex(random_bytes(16)); // Token para confirmación por email

// Insert reservation into database

$stmt = $conn->prepare("INSERT INTO reservations (name, last_name, email, phone, num_tickets, comments, privacy_accepted, ip_address, confirmation_code, confirmation_token, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'email_pending')");
$stmt->bind_param("sssisissss", $name, $last_name, $email, $phone, $num_tickets, $comments, $privacy_policy, $ip_address, $confirmation_code, $confirmation_token);

if ($stmt->execute()) {
    // Get the base URL for the confirmation link
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $baseURL = $protocol . "://" . $host;
    
    // Preparar datos para el email
    $email_data = [
        'name' => $name,
        'last_name' => $last_name,
        'email' => $email,
        'confirmation_code' => $confirmation_code,
        'confirmation_token' => $confirmation_token,
        'num_tickets' => $num_tickets
    ];
    
    // Enviar email de verificación usando el sistema de plantilla unificada
    if (send_template_email($email, EMAIL_TYPE_VERIFICATION, $email_data, $baseURL)) {
        // Regenerate CSRF token for security
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Success response
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // AJAX response
            echo json_encode([
                'success' => true, 
                'message' => '¡Reserva registrada con éxito! Hemos enviado un correo de confirmación a tu email. Por favor, revisa tu bandeja de entrada y confirma tu reserva haciendo clic en el enlace que te hemos enviado.',
                'confirmation_code' => $confirmation_code,
                'needs_email_confirmation' => true
            ]);
        } else {
            // Regular form submission - redirect to success page
            header('Location: index.php?success=1&email_confirmation=1&code=' . urlencode($confirmation_code) . '#entradas');
            exit;
        }
    } else {
        // Email failed but reservation saved
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // AJAX response
            echo json_encode([
                'success' => true, 
                'message' => '¡Reserva registrada con éxito! Sin embargo, hubo un problema al enviar el email de confirmación. Por favor, contacta con nosotros para completar el proceso de confirmación.',
                'confirmation_code' => $confirmation_code,
                'email_error' => true
            ]);
        } else {
            // Regular form submission - redirect to success page with warning
            header('Location: index.php?success=1&email_warning=1&email_confirmation=1&code=' . urlencode($confirmation_code) . '#entradas');
            exit;
        }
    }
} else {
    // Database error
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // AJAX response
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error al procesar la reserva. Por favor, inténtalo de nuevo más tarde.'
        ]);
    } else {
        // Regular form submission - redirect to error page
        header('Location: index.php?error=db&msg=' . urlencode('Error al procesar la reserva. Por favor, inténtalo de nuevo más tarde.') . '#entradas');
        exit;
    }
    
    // Log the error (in a production environment, this should go to a secure log)
    error_log('Error en la reserva: ' . $stmt->error);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>