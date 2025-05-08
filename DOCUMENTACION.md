# Sistema de Reservas - Cangrejos Albinos

Este documento proporciona instrucciones detalladas para instalar, configurar y mantener el sistema de reservas para el evento Cangrejos Albinos, tanto en entornos de desarrollo local como en el servidor de producción cangrejosalbinos.com.

## Índice

1. [Descripción General del Sistema](#descripción-general-del-sistema)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Requisitos del Sistema](#requisitos-del-sistema)
4. [Instalación en Entorno Local](#instalación-en-entorno-local)
5. [Configuración en Producción](#configuración-en-producción)
6. [Sistema de Emails](#sistema-de-emails)
7. [Panel de Administración](#panel-de-administración)
8. [Mantenimiento](#mantenimiento)
9. [Solución de Problemas](#solución-de-problemas)

## Descripción General del Sistema

El sistema de reservas Cangrejos Albinos es una aplicación web desarrollada en PHP y MySQL que permite a los usuarios reservar entradas para el evento "Cangrejos Albinos" en CACT Lanzarote. El sistema cuenta con:

- Formulario de reserva integrado en la página principal
- Proceso de confirmación por email en dos pasos
- Panel de administración para gestionar reservas
- Funcionalidad de exportación de datos
- Sistema de cancelación de reservas
- Gestión de disponibilidad de entradas

## Estructura del Proyecto

### Archivos Principales

| Archivo | Descripción |
|---------|-------------|
| `index.php` | Página principal del sitio con el formulario de reservas integrado |
| `process_reservation.php` | Procesa las solicitudes del formulario de reservas |
| `confirm_reservation.php` | Maneja la confirmación de reservas por email |
| `cancel_reservation.php` | Permite a los usuarios cancelar sus reservas |
| `includes/db_config.php` | Configuración de conexión a la base de datos |
| `includes/email/email_template.php` | Sistema de plantillas de email |
| `admin/index.php` | Página de inicio de sesión del panel de administración |
| `admin/dashboard.php` | Panel principal de administración |
| `admin/export.php` | Exportación de datos de reservas |
| `admin/reservas_stats.php` | Estadísticas de reservas |

### Archivos de Instalación y Configuración

| Archivo | Descripción |
|---------|-------------|
| `install.php` | Guía interactiva de instalación del sistema |
| `setup_database.php` | Configuración inicial de la base de datos |
| `update_database.php` | Actualización de la estructura de la base de datos |
| `update_db_password.php` | Utilidad para actualizar credenciales de MySQL |
| `database.sql` | Esquema principal de la base de datos |
| `database_update.sql` | Consultas para actualizar la estructura de la BD |
| `admin/check_users.php` | Verifica y crea usuarios administradores |

## Requisitos del Sistema

### Requisitos Mínimos

- PHP 7.2 o superior
- MySQL 5.7 o superior / MariaDB 10.2 o superior
- Extensión mysqli de PHP habilitada
- Función mail() de PHP habilitada o acceso a un servidor SMTP
- Servidor web Apache o Nginx

### Dependencias

- Bootstrap 4.5.2 (incluido)
- jQuery 3.5.1 (incluido)
- Fontawesome (incluido)

### Requisitos de Espacio y Recursos

- Espacio en disco: Aproximadamente 50MB para los archivos del sistema
- Base de datos: Tamaño inicial de aproximadamente 1MB, creciendo según el número de reservas
- Memoria: Configuración mínima de PHP con memory_limit=64M

## Instalación en Entorno Local

### 1. Preparación del Entorno

1. Configurar un servidor web local como XAMPP, WAMP, MAMP o similar.
2. Asegurarse de que PHP y MySQL están instalados y funcionando.
3. Crear un directorio para el proyecto en el directorio web del servidor.

### 2. Obtener el Código Fuente

```bash
# Clonar el repositorio Git
git clone [URL_DEL_REPOSITORIO] cangrejos_albinos

# Alternativa: Descargar y descomprimir el archivo zip del proyecto
```

### 3. Configuración de la Base de Datos

1. Crear una nueva base de datos en MySQL:
   ```sql
   CREATE DATABASE cangrejos_albinos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Configurar los parámetros de conexión:
   - Abrir el navegador y acceder a: `http://localhost/cangrejos_albinos/update_db_password.php`
   - Introducir los datos de conexión a MySQL (usuario, contraseña, host, puerto)
   - Hacer clic en "Actualizar Configuración"

3. Crear las tablas de la base de datos:
   - Acceder a: `http://localhost/cangrejos_albinos/setup_database.php`
   - Verificar que todas las tablas se han creado correctamente
   - Comprobar la creación del usuario administrador

### 4. Verificación de la Instalación

1. Acceder a la guía de instalación: `http://localhost/cangrejos_albinos/install.php`
2. Seguir las instrucciones y verificar que todos los requisitos se cumplen
3. Probar el formulario de reservas: `http://localhost/cangrejos_albinos/index.php#entradas`
4. Acceder al panel de administración: `http://localhost/cangrejos_albinos/admin/`
   - Usuario: `admin`
   - Contraseña: `change_me_immediately`

### 5. Configuración Adicional Local

1. Para probar los emails en entorno local, considerar:
   - Configurar un servidor de email local (ej. MailHog, Papercut)
   - Modificar las direcciones de email en `process_reservation.php` a una dirección donde se pueda probar
   - En Windows/XAMPP, configurar php.ini para usar SMTP local o externo

## Configuración en Producción

### 1. Transferencia de Archivos

1. Subir todos los archivos al servidor web mediante FTP o SSH:
   ```bash
   # Ejemplo con scp
   scp -r cangrejos_albinos/* usuario@cangrejosalbinos.com:/ruta/al/directorio/web/
   ```

2. Establecer los permisos correctos:
   ```bash
   # Directorios: 755, Archivos: 644
   find /ruta/al/directorio/web -type d -exec chmod 755 {} \;
   find /ruta/al/directorio/web -type f -exec chmod 644 {} \;
   ```

### 2. Configuración de la Base de Datos en Producción

1. Crear la base de datos en el servidor:
   - Usar phpMyAdmin o herramientas del panel de control del hosting
   - Crear un usuario específico con permisos limitados para mayor seguridad

2. Configurar la conexión:
   - Acceder a: `https://cangrejosalbinos.com/update_db_password.php`
   - Introducir las credenciales para el servidor de producción
   - Guardar los cambios

3. Crear las tablas:
   - Acceder a: `https://cangrejosalbinos.com/setup_database.php`
   - Verificar que todas las tablas se crean correctamente

4. Verificar el usuario administrador:
   - Acceder a: `https://cangrejosalbinos.com/admin/check_users.php`
   - Comprobar que el usuario administrador existe

### 3. Configuración Específica de Producción

1. Actualizar parámetros específicos del evento:

   - Editar `index.php` y `process_reservation.php` para establecer el número correcto de entradas:
     ```php
     $total_tickets_available = 100; // Cambiar a la capacidad real del evento
     ```

   - Actualizar la fecha del evento en las plantillas de email (`includes/email/email_template.php`):
     ```php
     <li><strong>Fecha del evento:</strong> 15 de mayo de 2025</li>
     <li><strong>Hora:</strong> 20:00</li>
     <li><strong>Lugar:</strong> Jameos del Agua, Lanzarote</li>
     ```

2. Configuración de seguridad:

   - Crear un archivo `.htaccess` en el directorio raíz:
     ```apache
     # Protección general
     Options -Indexes
     ServerSignature Off
     
     # Redirección a HTTPS
     RewriteEngine On
     RewriteCond %{HTTPS} off
     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
     
     # Proteger archivos sensibles
     <FilesMatch "(^\.htaccess|\.ini|\.log|(?<!\.min\.)\.js\.map)$">
         Order deny,allow
         Deny from all
     </FilesMatch>
     ```

   - Proteger el directorio de administración:
     ```apache
     # En /admin/.htaccess
     # Opcional: Protección por IP
     Order deny,allow
     Deny from all
     # Permitir solo ciertas IPs (cambiar por las IPs reales)
     Allow from 127.0.0.1
     Allow from 123.123.123.123
     ```

3. Cambiar la contraseña del administrador:
   - Acceder al panel de administración
   - Usar las credenciales provisionales para iniciar sesión
   - Cambiar inmediatamente la contraseña a una segura

### 4. Eliminación de Archivos de Instalación

Una vez configurado todo correctamente, eliminar los siguientes archivos:

```bash
rm install.php
rm setup_database.php
rm update_database.php
rm update_db_password.php
rm database.sql
rm database_update.sql
rm database_update_direct.sql
rm admin/check_users.php
rm admin/debug_login.php
```

## Sistema de Emails

El sistema de emails es crítico para el funcionamiento de las reservas, ya que gestiona las confirmaciones y cancelaciones.

### Estructura del Sistema de Emails

- `includes/email/email_template.php`: Contiene las plantillas de email y las funciones para enviarlos
- Tipos de emails implementados:
  - Verificación inicial (EMAIL_TYPE_VERIFICATION)
  - Confirmación final (EMAIL_TYPE_CONFIRMATION)
  - Cancelación (EMAIL_TYPE_CANCELLATION)
  - Notificación al administrador (EMAIL_TYPE_ADMIN_NOTIFICATION)

### Configuración del Sistema de Emails en Producción

1. **Actualizar dirección de remitente**:

   Editar `includes/email/email_template.php` alrededor de la línea 293:
   ```php
   if ($email_type == EMAIL_TYPE_ADMIN_NOTIFICATION) {
       $headers .= "From: Sistema de Reservas <noreply@cangrejosalbinos.com>" . "\r\n";
   } else {
       $headers .= "From: Cangrejos Albinos <noreply@cangrejosalbinos.com>" . "\r\n";
   }
   ```

2. **Actualizar email del administrador**:

   Editar `confirm_reservation.php` y `cancel_reservation.php`, buscar:
   ```php
   $admin_email = 'admin@cangrejos-albinos.com'; // Cambiar por el email real del administrador
   ```
   Y reemplazar por:
   ```php
   $admin_email = 'admin@cangrejosalbinos.com'; // O la dirección real para recibir notificaciones
   ```

3. **Verificar configuración de URLs**:

   Asegurarse de que las URLs en emails sean correctas revisando cómo se genera `$baseURL`:
   ```php
   // En process_reservation.php (alrededor de la línea 179)
   $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
   $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
   $baseURL = $protocol . "://" . $host;
   ```

4. **Implementación avanzada con PHPMailer (opcional pero recomendado)**:

   Para una solución más robusta, considerar la instalación de PHPMailer:
   
   a. Descargar PHPMailer desde https://github.com/PHPMailer/PHPMailer
   
   b. Crear el directorio `includes/phpmailer/` y extraer los archivos
   
   c. Crear un archivo `includes/mailer.php`:
   ```php
   <?php
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;
   
   require __DIR__ . '/phpmailer/src/Exception.php';
   require __DIR__ . '/phpmailer/src/PHPMailer.php';
   require __DIR__ . '/phpmailer/src/SMTP.php';
   
   function send_email($to, $subject, $body, $from_name = "Cangrejos Albinos", $from_email = "noreply@cangrejosalbinos.com") {
       $mail = new PHPMailer(true);
       
       try {
           // Configuración del servidor
           $mail->isSMTP();
           $mail->Host = 'smtp.cangrejosalbinos.com'; // Servidor SMTP
           $mail->SMTPAuth = true;
           $mail->Username = 'noreply@cangrejosalbinos.com'; // Usuario SMTP
           $mail->Password = 'contraseña-segura';     // Contraseña SMTP
           $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
           $mail->Port = 465;
           
           // Remitentes y destinatarios
           $mail->setFrom($from_email, $from_name);
           $mail->addAddress($to);
           
           // Contenido
           $mail->isHTML(true);
           $mail->Subject = $subject;
           $mail->Body = $body;
           $mail->CharSet = 'UTF-8';
           
           $mail->send();
           return true;
       } catch (Exception $e) {
           error_log("Error al enviar correo: {$mail->ErrorInfo}");
           return false;
       }
   }
   ```
   
   d. Modificar la función `send_template_email` en `includes/email/email_template.php`:
   ```php
   require_once __DIR__ . '/../mailer.php';
   
   function send_template_email($to, $email_type, $data, $baseURL = '') {
       // Generar el email
       $email = generate_email($email_type, $data, $baseURL);
       
       // Determinar el nombre del remitente según el tipo de email
       $from_name = ($email_type == EMAIL_TYPE_ADMIN_NOTIFICATION) 
           ? "Sistema de Reservas" 
           : "Cangrejos Albinos";
       
       // Enviar el email usando la nueva función
       return send_email($to, $email['subject'], $email['body'], $from_name);
   }
   ```

### Personalización de Plantillas de Email

Para modificar el contenido de los emails, editar las siguientes secciones en `includes/email/email_template.php`:

- **Email de Verificación**: Líneas ~52-80
- **Email de Confirmación Final**: Líneas ~83-119
- **Email de Cancelación**: Líneas ~122-144
- **Email de Notificación a Admin**: Líneas ~147-202

## Panel de Administración

El panel de administración permite gestionar las reservas, exportar datos y supervisar el evento.

### Acceso y Seguridad

- URL de acceso: `https://cangrejosalbinos.com/admin/`
- Credenciales iniciales:
  - Usuario: `admin`
  - Contraseña: `change_me_immediately` (cambiar inmediatamente tras primer acceso)

### Funcionalidades Principales

1. **Dashboard (`admin/dashboard.php`)**:
   - Visualización de todas las reservas
   - Filtrado por estado (confirmadas, pendientes, canceladas)
   - Búsqueda por nombre, email o DNI
   - Cambio de estado de reservas
   - Visualización del total de entradas reservadas

2. **Exportación de Datos (`admin/export.php`)**:
   - Exportación de reservas a CSV
   - Opciones de filtrado para la exportación

3. **Estadísticas (`admin/reservas_stats.php`)**:
   - Resumen de reservas por día
   - Gráficos de estado de reservas
   - Total de entradas reservadas vs. disponibles

### Gestión de Usuarios Administradores

Para crear usuarios administradores adicionales:

1. Acceder a `https://cangrejosalbinos.com/admin/check_users.php` (antes de eliminarlo)
2. Utilizar el formulario en la parte inferior para crear nuevos usuarios
3. Definir un nombre de usuario, contraseña y email

Alternativamente, insertar directamente en la base de datos:
```sql
INSERT INTO admin_users (username, password, email) VALUES 
('nuevo_admin', '$2y$10$...', 'nuevo@ejemplo.com');
-- La contraseña debe estar hasheada con password_hash() de PHP
```

## Mantenimiento

### Copias de Seguridad

Recomendaciones para copias de seguridad regulares:

1. **Base de datos**:
   ```bash
   # Exportar base de datos completa
   mysqldump -u usuario -p cangrejos_albinos > backup_$(date +%Y%m%d).sql
   
   # Importar en caso necesario
   mysql -u usuario -p cangrejos_albinos < backup_20250101.sql
   ```

2. **Archivos del sistema**:
   ```bash
   # Crear un archivo comprimido de todo el sistema
   tar -czvf cangrejos_backup_$(date +%Y%m%d).tar.gz /ruta/al/directorio/web/
   ```

3. **Automatización** (para servidores Linux):
   - Crear un script bash para las copias
   - Configurar una tarea cron para ejecutarlo regularmente

### Monitorización

1. **Supervisión de emails**:
   - Implementar registro de emails enviados
   - Revisar periódicamente los registros

2. **Verificación de disponibilidad**:
   - Comprobar regularmente que el sitio y el formulario están accesibles
   - Configurar servicio de monitorización como Uptime Robot

### Actualización

Para futuras actualizaciones del sistema:

1. Realizar una copia de seguridad completa antes de cualquier cambio
2. Probar los cambios en un entorno de desarrollo
3. Actualizar primero los archivos de menor impacto
4. Usar `update_database.php` para actualizaciones en la estructura de la BD

## Solución de Problemas

### Problemas Comunes y Soluciones

1. **Los emails no se envían**:
   - Verificar la configuración del servidor de email
   - Comprobar los registros de error de PHP
   - Usar la herramienta `mail()` o implementar PHPMailer
   - Verificar que las direcciones de email estén correctamente formateadas

2. **Errores de conexión a la base de datos**:
   - Verificar credenciales en `includes/db_config.php`
   - Comprobar que el servicio MySQL está activo
   - Verificar permisos del usuario de la base de datos

3. **Problemas con las reservas**:
   - Revisar los mensajes de error en el formulario
   - Verificar la disponibilidad de entradas en la tabla `reservations`
   - Comprobar que los tokens de CSRF se generan correctamente

### Registro de Errores

Para una mejor depuración:

1. **Activar registro de errores de PHP**:
   ```php
   // Añadir al inicio de los archivos principales
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ini_set('error_log', '/ruta/a/error_log.php');
   ```

2. **Implementar registro personalizado**:
   ```php
   // Función para registro de errores
   function log_error($message) {
       $log_file = __DIR__ . '/logs/system.log';
       $date = date('Y-m-d H:i:s');
       file_put_contents($log_file, "[$date] $message" . PHP_EOL, FILE_APPEND);
   }
   ```

---

*Este documento fue generado para el Sistema de Reservas de Cangrejos Albinos. © 2025 CACT Lanzarote. Para soporte o consultas adicionales, contactar con el desarrollador.*