<?php
/**
 * Sistema de plantilla de email unificada
 * Una plantilla HTML simple que funciona en todos los navegadores y clientes de email
 */

// Definir constantes para tipos de email
define('EMAIL_TYPE_VERIFICATION', 'verification');
define('EMAIL_TYPE_CONFIRMATION', 'confirmation');
define('EMAIL_TYPE_CANCELLATION', 'cancellation');
define('EMAIL_TYPE_ADMIN_NOTIFICATION', 'admin_notification');
define('EMAIL_TYPE_NEWSLETTER_CONFIRMATION', 'newsletter_confirmation');

/**
 * Genera un email HTML basado en el tipo y los datos proporcionados
 * 
 * @param string $email_type El tipo de email (usar constantes definidas)
 * @param array $data Datos para el email (nombre, apellidos, código de confirmación, etc.)
 * @param string $baseURL La URL base del sitio
 * @return array Arreglo con subject y body del email
 */
function generate_email($email_type, $data, $baseURL = '') {
    // Logo de CACT Lanzarote
    $logo_url = 'https://imgcact.b-cdn.net/spai/ret_img/cactlanzarote.com/wp-content/uploads/2022/04/logo.svg';
    
    // Si no se proporciona URL base, intentar detectarla
    if (empty($baseURL)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $baseURL = $protocol . "://" . $host;
    }
    
    // Valor por defecto para nombre completo
    $full_name = isset($data['name']) && isset($data['last_name']) 
        ? $data['name'] . ' ' . $data['last_name']
        : (isset($data['full_name']) ? $data['full_name'] : 'Usuario');
    
    // Inicializar variables
    $subject = '';
    $title = '';
    $main_content = '';
    $details_content = '';
    $action_button = '';
    $footer_content = '';
    
    // Configurar contenido según el tipo de email
    switch ($email_type) {
        case EMAIL_TYPE_NEWSLETTER_CONFIRMATION:
            $subject = "Confirma tu suscripción al newsletter - Cangrejos Albinos";
            $title = "Confirma tu suscripción";
            $confirmationURL = $baseURL . "/confirm_newsletter.php?token=" . $data['confirmation_token'];
            
            $main_content = "
                <p>Hola,</p>
                <p>Gracias por suscribirte al newsletter de \"Cangrejos Albinos\". <strong>Para completar tu suscripción y comenzar a recibir nuestras novedades, confirma tu dirección de correo electrónico haciendo clic en el botón a continuación:</strong></p>
            ";
            
            $action_button = "
                <div style=\"text-align: center; margin: 30px 0;\">
                    <a href=\"{$confirmationURL}\" style=\"background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;\">Confirmar mi suscripción</a>
                </div>
                <p style=\"font-size: 13px; color: #666;\">Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                <p style=\"font-size: 13px; word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px;\">{$confirmationURL}</p>
            ";
            
            $details_content = "";
            
            $footer_content = "
                <p><strong>Importante:</strong> Si no has solicitado esta suscripción, simplemente ignora este mensaje.</p>
                <p>Respetamos tu privacidad y puedes darte de baja en cualquier momento con un solo clic desde cualquiera de nuestros emails.</p>
                <p>¡Gracias por tu interés en Cangrejos Albinos!</p>
            ";
            break;
            
        case EMAIL_TYPE_VERIFICATION:
            $subject = "Confirmación de reserva - Cangrejos Albinos";
            $title = "Confirmación Pendiente";
            $confirmationURL = $baseURL . "/confirm_reservation.php?token=" . $data['confirmation_token'];
            
            $main_content = "
                <p>Hola <strong>{$full_name}</strong>,</p>
                <p>Hemos recibido tu solicitud de reserva para el evento \"Cangrejos Albinos\". <strong>Para completar tu reserva, es necesario que confirmes tu dirección de correo electrónico haciendo clic en el botón a continuación:</strong></p>
            ";
            
            $action_button = "
                <div style=\"text-align: center; margin: 30px 0;\">
                    <a href=\"{$confirmationURL}\" style=\"background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;\">Confirmar mi reserva</a>
                </div>
                <p style=\"font-size: 13px; color: #666;\">Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                <p style=\"font-size: 13px; word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px;\">{$confirmationURL}</p>
            ";
            
            $details_content = "
                <h3 style=\"margin-top: 0; color: #333;\">Detalles de la reserva:</h3>
                <ul style=\"padding-left: 20px;\">
                    <li><strong>Número de confirmación:</strong> {$data['confirmation_code']}</li>
                    <li><strong>Número de entradas:</strong> {$data['num_tickets']}</li>
                    <li><strong>Fecha del evento:</strong> 15 de mayo de 2025</li>
                    <li><strong>Hora:</strong> 20:00</li>
                    <li><strong>Lugar:</strong> Jameos del Agua, Lanzarote</li>
                </ul>
            ";
            
            $footer_content = "
                <p><strong>Importante:</strong> Tu reserva no estará completa hasta que confirmes tu dirección de correo electrónico.</p>
                <p>Si tienes alguna pregunta o necesitas ayuda, por favor contáctanos respondiendo a este correo o llamando al +34 928 849 444.</p>
                <p>¡Gracias por tu interés en Cangrejos Albinos!</p>
            ";
            break;
            
        case EMAIL_TYPE_CONFIRMATION:
            $subject = "Reserva Confirmada - Cangrejos Albinos";
            $title = "¡Reserva Confirmada!";
            
            // Generar enlace de cancelación
            $cancellationURL = $baseURL . "/cancel_reservation.php?code=" . $data['confirmation_code'] . "&email=" . urlencode($data['email']);
            
            $main_content = "
                <p>Hola <strong>{$full_name}</strong>,</p>
                <p>Tu reserva para el evento \"Cangrejos Albinos\" ha sido confirmada con éxito.</p>
            ";
            
            $details_content = "
                <h3 style=\"margin-top: 0; color: #333;\">Detalles de la reserva:</h3>
                <ul style=\"padding-left: 20px;\">
                    <li><strong>Número de confirmación:</strong> {$data['confirmation_code']}</li>
                    <li><strong>Número de entradas:</strong> {$data['num_tickets']}</li>
                    <li><strong>Fecha del evento:</strong> 15 de mayo de 2025</li>
                    <li><strong>Hora:</strong> 20:00</li>
                    <li><strong>Lugar:</strong> Jameos del Agua, Lanzarote</li>
                </ul>
            ";
            
            $action_button = "
                <div style=\"margin-top: 20px; margin-bottom: 20px; text-align: center;\">
                    <p><strong>¿No podrás asistir?</strong></p>
                    <p>Si no puedes asistir al evento, te agradecemos que canceles tu reserva para que otras personas puedan disfrutar del espectáculo.</p>
                    <a href=\"{$cancellationURL}\" style=\"display: inline-block; background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;\">Cancelar mi reserva</a>
                </div>
            ";
            
            $footer_content = "
                <p>Te recomendamos guardar este email o imprimirlo y traerlo contigo el día del evento.</p>
                <p>Recuerda también traer tu DNI/NIE para la verificación a la entrada.</p>
                <p>Si tienes alguna pregunta, por favor contáctanos respondiendo a este correo o llamando al +34 928 849 444.</p>
                <p>¡Gracias por tu reserva y esperamos verte pronto!</p>
            ";
            break;
            
        case EMAIL_TYPE_CANCELLATION:
            $subject = "Cancelación de reserva - Cangrejos Albinos";
            $title = "Reserva Cancelada";
            
            $main_content = "
                <p>Hola <strong>{$full_name}</strong>,</p>
                <p>Confirmamos que tu reserva para el evento \"Cangrejos Albinos\" ha sido cancelada según tu solicitud.</p>
            ";
            
            $details_content = "
                <h3 style=\"margin-top: 0; color: #333;\">Detalles de la reserva cancelada:</h3>
                <ul style=\"padding-left: 20px;\">
                    <li><strong>Número de confirmación:</strong> {$data['confirmation_code']}</li>
                    <li><strong>Número de entradas:</strong> {$data['num_tickets']}</li>
                    <li><strong>Fecha del evento:</strong> 15 de mayo de 2025</li>
                </ul>
            ";
            
            $footer_content = "
                <p>Si has cancelado por error y deseas hacer una nueva reserva, por favor visita nuestra página web.</p>
                <p>Gracias por informarnos con anticipación. Esperamos verte en futuros eventos.</p>
                <p>Atentamente,<br>El equipo de CACT Lanzarote</p>
            ";
            break;
            
        case EMAIL_TYPE_ADMIN_NOTIFICATION:
            $action_type = isset($data['action_type']) ? $data['action_type'] : 'default';
            
            switch ($action_type) {
                case 'new_reservation':
                    $subject = "Nueva confirmación de reserva - Cangrejos Albinos";
                    $title = "Nueva reserva confirmada";
                    $main_content = "
                        <p>Un usuario ha confirmado su reserva para el evento Cangrejos Albinos.</p>
                    ";
                    break;
                    
                case 'cancellation':
                    $subject = "Cancelación de reserva por usuario - Cangrejos Albinos";
                    $title = "Cancelación de Reserva por Usuario";
                    $main_content = "
                        <p>Un usuario ha cancelado su reserva para el evento Cangrejos Albinos.</p>
                    ";
                    break;
                    
                default:
                    $subject = "Notificación de reserva - Cangrejos Albinos";
                    $title = "Notificación de Reserva";
                    $main_content = "
                        <p>Hay una actualización en el sistema de reservas.</p>
                    ";
                    break;
            }
            
            // Contenido específico para admin
            $details_content = "
                <h3>Detalles de la reserva:</h3>
                <ul>
                    <li><strong>ID de reserva:</strong> {$data['id']}</li>
                    <li><strong>Nombre:</strong> {$full_name}</li>
                    <li><strong>Email:</strong> {$data['email']}</li>
                    <li><strong>Teléfono:</strong> {$data['phone']}</li>
                    <li><strong>DNI/NIF:</strong> {$data['dni']}</li>
                    <li><strong>Número de entradas:</strong> {$data['num_tickets']}" . 
                    ($action_type == 'cancellation' ? " (liberadas)" : "") . "</li>
                    <li><strong>Fecha de reserva:</strong> " . (isset($data['reservation_date']) ? date('d/m/Y H:i', strtotime($data['reservation_date'])) : date('d/m/Y H:i')) . "</li>
                    " . ($action_type == 'cancellation' ? "<li><strong>Fecha de cancelación:</strong> " . date('d/m/Y H:i') . "</li>" : "") . "
                </ul>
            ";
            
            // Footer para admin
            if ($action_type == 'cancellation') {
                $footer_content = "
                    <p>Estas entradas han sido liberadas y están disponibles nuevamente para otras reservas.</p>
                ";
            } else {
                $footer_content = "
                    <p>Esta reserva ha sido confirmada automáticamente después de la verificación del email del usuario. Puedes gestionarla o revisarla desde el <a href='{$baseURL}/admin/dashboard.php'>panel de administración</a>.</p>
                ";
            }
            break;
    }
    
    // Construir plantilla HTML completa
    $body = "
    <html>
    <head>
        <title>{$title}</title>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
        <style type=\"text/css\">
            body, html { 
                margin: 0; 
                padding: 0; 
                font-family: Arial, Helvetica, sans-serif; 
                font-size: 16px; 
                line-height: 1.5;
                color: #333333;
            }
            * { box-sizing: border-box; }
            img { max-width: 100%; }
            h1, h2, h3, h4 { margin-top: 0; color: #333333; }
            p { margin: 10px 0; }
            a { color: #0066cc; text-decoration: underline; }
            .email-container { max-width: 600px; margin: 0 auto; }
            .email-header { background-color: #f8f8f8; padding: 20px; text-align: center; }
            .email-body { padding: 20px; background-color: #ffffff; }
            .email-footer { padding: 20px; background-color: #f8f8f8; font-size: 12px; color: #666666; text-align: center; }
            .details-box { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: #ffffff !important; text-decoration: none; border-radius: 4px; font-weight: bold; }
            .button-red { background-color: #dc3545; }
            @media screen and (max-width: 600px) {
                .email-container { width: 100% !important; }
                .email-body, .email-footer, .email-header { padding: 15px 10px !important; }
            }
        </style>
    </head>
    <body>
        <div class=\"email-container\">
            <div class=\"email-header\">
                <img src=\"{$logo_url}\" alt=\"CACT Lanzarote\" style=\"max-width: 200px;\">
            </div>
            
            <div class=\"email-body\">
                <h2 style=\"text-align: center;\">{$title}</h2>
                
                {$main_content}
                
                <div class=\"details-box\">
                    {$details_content}
                </div>
                
                {$action_button}
                
                {$footer_content}
            </div>
            
            <div class=\"email-footer\">
                <p>CACT Lanzarote - Centros de Arte, Cultura y Turismo</p>
                <p>Este email ha sido enviado relacionado con tu reserva para el evento \"Cangrejos Albinos\".</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return [
        'subject' => $subject,
        'body' => $body
    ];
}

 /**
   * Función para enviar un email usando la plantilla
   *
   * @param string $to Email del destinatario
   * @param string $email_type Tipo de email
   * @param array $data Datos para el email
   * @param string $baseURL URL base del sitio
   * @return boolean Resultado del envío
   */
  function send_template_email($to, $email_type, $data, $baseURL = '') {
    // Incluir el sistema de PHPMailer
    require_once __DIR__ . '/../mailer.php';

    // Generar el email
    $email = generate_email($email_type, $data, $baseURL);

    // Determinar el nombre y email del remitente según el tipo
    if ($email_type == EMAIL_TYPE_ADMIN_NOTIFICATION) {
        $from_name = 'Sistema de Reservas';
    } else {
        $from_name = 'Cangrejos Albinos';
    }
    $from_email = 'no-reply@cangrejosalbinos.com'; // Cambia esto a tu email real

    // Enviar el email usando PHPMailer
    return send_email_phpmailer($to, $email['subject'], $email['body'], $from_name, $from_email);
}
?>