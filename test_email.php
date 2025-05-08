<?php
  // Incluir archivo de configuración para PHPMailer
  require_once 'includes/mailer.php';

  // Dirección de prueba - cambia esto a tu propia dirección para pruebas
  $test_email = 'manuel@kibostudios.com';

  // Probar PHPMailer
  $result = send_email_phpmailer(
      $test_email,
      'Prueba de PHPMailer - Cangrejos Albinos',
      '<html><body><h1>Prueba de PHPMailer</h1><p>Este es un correo de prueba enviado usando PHPMailer.</p></body></html>',
      'Cangrejos Albinos',
      'no-reply@cangrejosalbinos.com'
  );

  // Mostrar resultado
  if ($result) {
      echo "<h2 style='color:green'>Correo enviado correctamente con PHPMailer</h2>";
  } else {
      echo "<h2 style='color:red'>Error al enviar correo con PHPMailer</h2>";
      echo "<p>Verifica las credenciales SMTP y la configuración del servidor.</p>";
  }
  ?>