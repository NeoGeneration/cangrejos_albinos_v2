<?php
  /**
   * Sistema de envío de correos electrónicos utilizando PHPMailer
   * Para un envío más robusto y con mejor manejo de errores
   */

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;

  // Cargar PHPMailer - ajusta la ruta según cómo lo hayas instalado
  if (file_exists(__DIR__ . '/phpmailer/src/Exception.php')) {
      // Instalación manual
      require_once __DIR__ . '/phpmailer/src/Exception.php';
      require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
      require_once __DIR__ . '/phpmailer/src/SMTP.php';
  } elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
      // Instalación con Composer
      require_once __DIR__ . '/../vendor/autoload.php';
  } else {
      // Si no se encuentra PHPMailer, usar la función mail() como fallback
      function send_email_phpmailer($to, $subject, $body, $from_name = 'Cangrejos Albinos', $from_email = 'noreply@cangrejosalbinos.com') {
          $headers = "MIME-Version: 1.0" . "\r\n";
          $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
          $headers .= "From: " . $from_name . " <" . $from_email . ">" . "\r\n";

          return mail($to, $subject, $body, $headers);
      }

      // Terminar aquí para usar mail() si PHPMailer no está disponible
      return;
  }

  /**
   * Envía un correo electrónico usando PHPMailer
   *
   * @param string $to Email del destinatario
   * @param string $subject Asunto del correo
   * @param string $body Cuerpo HTML del correo
   * @param string $from_name Nombre del remitente
   * @param string $from_email Email del remitente
   * @return boolean Éxito o fracaso del envío
   */
  function send_email_phpmailer($to, $subject, $body, $from_name = 'Cangrejos Albinos', $from_email = 'noreply@cangrejosalbinos.com') {
      // Configuración de credenciales SMTP - Modifica estos valores con tus credenciales reales
      $smtp_host = 'smtp.hostinger.com';
      $smtp_username = 'no-reply@cangrejosalbinos.com'; // Cambia esto a tu dirección de correo real
      $smtp_password = '8B[b5$0['; // Cambia esto a tu contraseña
      $smtp_port = 465;
      $smtp_secure = 'ssl';

      // Crear una instancia de PHPMailer
      $mail = new PHPMailer(true);

      try {
          // Configuración del servidor
          $mail->isSMTP();                                      // Usar SMTP
          $mail->Host       = $smtp_host;                       // Servidor SMTP
          $mail->SMTPAuth   = true;                             // Habilitar autenticación SMTP
          $mail->Username   = $smtp_username;                   // Usuario SMTP
          $mail->Password   = $smtp_password;                   // Contraseña SMTP
          $mail->SMTPSecure = $smtp_secure;                     // Habilitar cifrado SSL
          $mail->Port       = $smtp_port;                       // Puerto TCP
          $mail->CharSet    = 'UTF-8';                          // Codificación UTF-8

          // Para depuración - Comentar en producción
          // $mail->SMTPDebug = SMTP::DEBUG_SERVER;             // Habilitar salida de depuración

          // Remitentes y destinatarios
          $mail->setFrom($from_email, $from_name);              // Remitente
          $mail->addAddress($to);                               // Destinatario

          // Contenido
          $mail->isHTML(true);                                  // Formato HTML
          $mail->Subject = $subject;                            // Asunto
          $mail->Body    = $body;                               // Cuerpo HTML

          // Enviar el email
          $mail->send();
          return true;
      } catch (Exception $e) {
          // Registrar el error para depuración
          //error_log("Error al enviar email con PHPMailer: {$mail->ErrorInfo}");
        return "Error PHPMailer: " . $mail->ErrorInfo;
          //return false;
      }
  }