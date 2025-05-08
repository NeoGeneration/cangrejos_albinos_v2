<?php
// This file is for testing the email template system

// Include the email template system
require_once 'includes/email/email_template.php';

// Test data for a sample reservation
$test_data = [
    'id' => 123,
    'name' => 'Juan',
    'last_name' => 'Pérez',
    'email' => 'test@example.com',
    'phone' => '123456789',
    'dni' => '12345678A',
    'confirmation_code' => md5('test'),
    'confirmation_token' => md5('test_token'),
    'num_tickets' => 2,
    'reservation_date' => date('Y-m-d H:i:s')
];

// Base URL for links
$baseURL = 'http://localhost';

echo "<h1>Test de Sistema de Plantillas de Email</h1>";

// Test email types
$email_types = [
    EMAIL_TYPE_VERIFICATION, 
    EMAIL_TYPE_CONFIRMATION, 
    EMAIL_TYPE_CANCELLATION, 
    EMAIL_TYPE_ADMIN_NOTIFICATION
];

foreach ($email_types as $type) {
    // For admin notification, add action type
    if ($type === EMAIL_TYPE_ADMIN_NOTIFICATION) {
        $test_data['action_type'] = 'new_reservation';
        echo "<h2>Admin Notification Email - New Reservation</h2>";
        
        $email = generate_email($type, $test_data, $baseURL);
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($email['subject']) . "</p>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 30px;'>" . $email['body'] . "</div>";
        
        $test_data['action_type'] = 'cancellation';
        echo "<h2>Admin Notification Email - Cancellation</h2>";
        
        $email = generate_email($type, $test_data, $baseURL);
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($email['subject']) . "</p>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 30px;'>" . $email['body'] . "</div>";
    } else {
        // Get email template for the type
        echo "<h2>" . ucfirst($type) . " Email</h2>";
        
        $email = generate_email($type, $test_data, $baseURL);
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($email['subject']) . "</p>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 30px;'>" . $email['body'] . "</div>";
    }
}

// Instructions
echo "<h2>Instrucciones para el uso del sistema de plantillas</h2>";
echo "<p>Para utilizar el sistema de plantillas de email en cualquier parte de la aplicación:</p>";
echo "<ol>";
echo "<li>Incluir el archivo: <code>require_once 'includes/email/email_template.php';</code></li>";
echo "<li>Crear un array con los datos del usuario y la reserva</li>";
echo "<li>Llamar a la función <code>send_template_email(\$to, \$email_type, \$data, \$baseURL)</code></li>";
echo "</ol>";

echo "<p>Ejemplo:</p>";
echo "<pre>
// Datos para el email
\$email_data = [
    'id' => \$reservation['id'],
    'name' => \$reservation['name'],
    'last_name' => \$reservation['last_name'],
    'email' => \$reservation['email'],
    'confirmation_code' => \$reservation['confirmation_code'],
    'num_tickets' => \$reservation['num_tickets']
];

// Enviar email de verificación
send_template_email(\$email, EMAIL_TYPE_VERIFICATION, \$email_data, \$baseURL);
</pre>";
?>