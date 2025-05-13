<?php
// send_clarification_email.php
// Script para enviar un email aclaratorio a los usuarios confirmados sobre el cambio de fecha/hora del evento

require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/includes/mailer.php';

// Configura aquí el asunto y el cuerpo del email aclaratorio
$subject = 'IMPORTANTE: Corrección de fecha y hora del evento Cangrejos Albinos';

// --- Plantilla HTML replicando la estructura de email_template.php ---
function build_clarification_email($nombre, $mensaje_html) {
    $logo_url = 'https://cangrejosalbinos.com/assets/img/email/CACT_logotipo.png';
    $title = 'Aclaración sobre la fecha y hora del evento';
    $body = "<html>\n<head>\n<title>{$title}</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />\n<style type=\"text/css\">\nbody, html { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.5; color: #333333; }\n* { box-sizing: border-box; }\nimg { max-width: 100%; }\nh1, h2, h3, h4 { margin-top: 0; color: #333333; }\np { margin: 10px 0; }\na { color: #0066cc; text-decoration: underline; }\n.email-container { max-width: 600px; margin: 0 auto; }\n.email-header { background-color: #f8f8f8; padding: 20px; text-align: center; }\n.email-body { padding: 20px; background-color: #ffffff; }\n.email-footer { padding: 20px; background-color: #f8f8f8; font-size: 12px; color: #666666; text-align: center; }\n.details-box { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }\n@media screen and (max-width: 600px) { .email-container { width: 100% !important; } .email-body, .email-footer, .email-header { padding: 15px 10px !important; } }\n</style>\n</head>\n<body>\n<div class=\"email-container\">\n<div class=\"email-header\">\n<img src=\"{$logo_url}\" alt=\"CACT Lanzarote\" style=\"max-width: 200px;\">\n</div>\n<div class=\"email-body\">\n<h2 style=\"text-align: center;\">{$title}</h2>\n<div class=\"details-box\">\n{$mensaje_html}\n</div>\n</div>\n<div class=\"email-footer\">\n<p>CACT Lanzarote - Centros de Arte, Cultura y Turismo</p>\n<p>Este email ha sido enviado relacionado con tu reserva para el evento \"Cangrejos Albinos\".</p>\n</div>\n</div>\n</body>\n</html>";
    return $body;
}

// Puedes personalizar el mensaje aquí. Usa {NOMBRE} como placeholder si quieres personalizar con el nombre del usuario.
$body_template = '<p>Hola {NOMBRE},</p>
<p>Te escribimos para informarte de una corrección importante: la fecha y hora del evento <strong>Cangrejos Albinos</strong> que informamos anteriormente en la confirmación por email era incorrecta.</p>
<p>La fecha y hora correctas son: <strong>17 de mayo de 2025, a las 20:30</strong>.</p>
<p>Sentimos la confusión y agradecemos tu comprensión. Si tienes cualquier duda, puedes responder a este correo.</p>
<p>¡Nos vemos en el evento!</p>
<p>Equipo Cangrejos Albinos</p>';

// --- MODO PRUEBA: solo enviar a galeus@gmail.com ---
$modo_prueba = true; // Cambia a false para enviar a todos los confirmados

if ($modo_prueba) {
    // Solo un destinatario de prueba
    $usuarios = [
        [
            'name' => 'Jorge (Prueba)',
            'email' => 'galeus@gmail.com'
        ]
    ];
} else {
    // Envío real a todos los confirmados
    $sql = "SELECT id, name, email FROM reservations WHERE status = 'confirmed'";
    $result = $conn->query($sql);
    if (!$result) {
        die('Error al consultar la base de datos: ' . $conn->error);
    }
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

$total = 0;
$enviados = 0;
$fallidos = 0;

foreach ($usuarios as $row) {
    $total++;
    $nombre = $row['name'] ?: 'asistente';
    $to = $row['email'];
    $mensaje_html = str_replace('{NOMBRE}', htmlspecialchars($nombre), $body_template);
    $body = build_clarification_email($nombre, $mensaje_html);

    // Forzar el remitente correcto (solo nombre personalizable)
    $from_name = 'Cangrejos Albinos';
    $from_email = 'no-reply@cangrejosalbinos.com';
    $ok = send_email_phpmailer($to, $subject, $body, $from_name, $from_email);
    if ($ok === true) {
        echo "Enviado a: $to<br>\n";
        $enviados++;
    } else {
        echo "<span style='color:red'>Fallo al enviar a: $to</span><br>\n";
        if (is_string($ok)) {
            echo '<pre>' . htmlspecialchars($ok) . '</pre>';
        }
        $fallidos++;
    }
    // Opcional: sleep(1); // para evitar bloqueos por spam
}

echo "<hr>\n";
echo "Total procesados: $total<br>\n";
echo "Enviados correctamente: $enviados<br>\n";
echo "Fallidos: $fallidos<br>\n";

$conn->close();
