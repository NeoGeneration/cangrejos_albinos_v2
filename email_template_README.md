# Sistema de Plantillas de Email Unificado

Este sistema proporciona una forma sencilla y unificada de enviar emails HTML para diferentes propósitos del sistema de reservas.

## Descripción General

El sistema de plantillas de email está diseñado para:

1. Mantener una apariencia consistente en todos los emails
2. Simplificar el mantenimiento de los emails
3. Asegurar compatibilidad con todos los clientes de email
4. Reducir la duplicación de código

## Estructura

- `/includes/email/email_template.php` - Archivo principal del sistema de plantillas

## Tipos de Emails

El sistema soporta los siguientes tipos de emails:

1. **EMAIL_TYPE_VERIFICATION** - Email de verificación enviado cuando el usuario hace una reserva
2. **EMAIL_TYPE_CONFIRMATION** - Email de confirmación enviado cuando el usuario verifica su email
3. **EMAIL_TYPE_CANCELLATION** - Email de cancelación enviado cuando una reserva es cancelada
4. **EMAIL_TYPE_ADMIN_NOTIFICATION** - Notificaciones administrativas (nuevas reservas, cancelaciones)

## Cómo Usar

Para usar el sistema de plantillas en cualquier parte de la aplicación:

```php
// Incluir el sistema de plantillas
require_once 'includes/email/email_template.php';

// Obtener la URL base para los enlaces
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$baseURL = $protocol . "://" . $host;

// Preparar datos para el email
$email_data = [
    'id' => $reservation['id'],
    'name' => $reservation['name'],
    'last_name' => $reservation['last_name'],
    'email' => $reservation['email'],
    'phone' => $reservation['phone'],
    'dni' => $reservation['dni'],
    'confirmation_code' => $reservation['confirmation_code'],
    'confirmation_token' => $reservation['confirmation_token'], // Solo para email de verificación
    'num_tickets' => $reservation['num_tickets'],
    'reservation_date' => $reservation['reservation_date']
];

// Para notificaciones de admin, añadir el tipo de acción
if ($email_type === EMAIL_TYPE_ADMIN_NOTIFICATION) {
    $email_data['action_type'] = 'new_reservation'; // o 'cancellation'
}

// Enviar el email utilizando el sistema de plantillas
send_template_email($to_email, $email_type, $email_data, $baseURL);
```

## Funciones Principales

### `generate_email($email_type, $data, $baseURL)`

Genera el contenido HTML y asunto para un email específico.

- **Parámetros:**
  - `$email_type`: Tipo de email (usar constantes definidas)
  - `$data`: Array con los datos para el email
  - `$baseURL`: URL base para los enlaces en el email

- **Retorna:** Array con 'subject' y 'body'

### `send_template_email($to, $email_type, $data, $baseURL)`

Envía un email utilizando la plantilla generada.

- **Parámetros:**
  - `$to`: Dirección de email del destinatario
  - `$email_type`: Tipo de email
  - `$data`: Array con los datos para el email
  - `$baseURL`: URL base para los enlaces

- **Retorna:** Booleano indicando éxito o fracaso

## Pruebas

Puede probar el sistema de plantillas accediendo a:
`http://tu-sitio/test_email_template.php`

Este archivo muestra cómo se ven los diferentes tipos de emails generados por el sistema.

## Modificación de Plantillas

Para modificar el aspecto o contenido de los emails, edite el archivo `email_template.php` y actualice:

1. El diseño HTML base en la función `generate_email()`
2. El contenido específico para cada tipo de email en el switch statement
3. Los estilos CSS en la etiqueta `<style>` dentro de la plantilla

## Ventajas del Sistema Unificado

- **Mantenimiento simplificado:** Solo hay un lugar para realizar cambios
- **Consistencia:** Todos los emails tienen el mismo aspecto y estilo
- **Compatibilidad:** Diseño probado para funcionar en la mayoría de clientes de email
- **Flexibilidad:** Fácil de extender para nuevos tipos de emails en el futuro