# CLAUDE.md

Guía para Claude Code al trabajar con este repositorio.

## Proyecto

**Cangrejos Albinos** — Sistema de reservas web para eventos de CACT Lanzarote (Centros de Arte, Cultura y Turismo) en Jameos del Agua.

- **Repo:** https://github.com/NeoGeneration/cangrejos_albinos_26
- **Producción:** https://cangrejosalbinos.com/
- **Hosting:** Hostinger
- **Admin:** https://cangrejosalbinos.com/admin/

## Stack

- **Backend:** PHP 7.2+, MySQL 5.7+ / MariaDB 10.2+
- **Frontend:** Bootstrap 4.5.2, jQuery, Fontawesome, Swiper, Magnific Popup, WOW.js
- **Email:** PHPMailer + SMTP Hostinger (smtp.hostinger.com:465/SSL)
- **Dependencias PHP:** vlucas/phpdotenv ^5.6 (Composer)
- **Cookies/GDPR:** Klaro Cookie Consent Manager
- **Analytics:** Google Analytics (G-96MVM31JD0, con consentimiento Klaro)

## Estructura del proyecto

```
cangrejos_albinos_26/
├── index.php                       # Landing principal + formulario de reservas (~1400 líneas)
├── process_reservation.php         # Procesa reservas (validación, BD, email)
├── confirm_reservation.php         # Confirma reserva vía token en email
├── cancel_reservation.php          # Cancelación de reserva
├── process_newsletter.php          # Suscripción newsletter (usa PDO)
├── confirm_newsletter.php          # Confirmación newsletter vía token
├── send_clarification_email.php    # Envío masivo de emails aclaratorios
├── timezone_check.php              # Debug de zona horaria PHP/MySQL
│
├── admin/
│   ├── index.php                   # Login admin (CSRF + password_verify)
│   ├── dashboard.php               # Gestión de reservas (paginación, filtros, edición)
│   ├── export.php                  # Exportación CSV (UTF-8 BOM)
│   ├── reservas_stats.php          # Estadísticas (capacidad, confirmadas, pendientes)
│   └── logout.php
│
├── includes/
│   ├── db_config.php               # Conexión BD (dotenv, detecta env por HTTP_HOST)
│   ├── event_config.php            # Configuración centralizada del evento
│   ├── mailer.php                  # PHPMailer wrapper (send_email_phpmailer)
│   ├── email/
│   │   └── email_template.php      # Plantillas HTML unificadas (generate_email, send_template_email)
│   └── phpmailer/src/              # Librería PHPMailer
│
├── assets/
│   ├── css/                        # bootstrap, main.css, newsletter.css, klaro.css, etc.
│   ├── js/                         # main.js, newsletter.js, klaro-config.js, etc.
│   └── img/                        # Imágenes organizadas (hero/, banner/, email/, schedule/)
│
├── backup/                         # Respaldos y templates antiguos
├── vendor/                         # Dependencias Composer (phpdotenv)
│
├── entradas-angel-leon.html        # Páginas individuales de eventos
├── entradas-elsa-punset.html
├── entradas-inaki-gabilondo.html
│
├── .env.production                 # Variables de entorno producción
├── .env.local                      # Variables de entorno local (gitignored)
├── composer.json / composer.lock
├── .htaccess                       # Seguridad Apache + protección de archivos
└── .gitignore
```

## Base de datos

### Tablas

**reservations**
| Campo | Tipo | Notas |
|---|---|---|
| id | INT AUTO_INCREMENT | PK |
| name, last_name | VARCHAR(100) | Obligatorios |
| email | VARCHAR(255) | UNIQUE |
| phone | VARCHAR(20) | Min 9 dígitos |
| num_tickets | INT | 1-4 (configurable) |
| comments | TEXT | Opcional |
| privacy_accepted | TINYINT(1) | |
| ip_address | VARCHAR(45) | |
| confirmation_code | VARCHAR(32) | UNIQUE, MD5 |
| confirmation_token | VARCHAR(255) | UNIQUE, se anula al confirmar |
| email_confirmed | TINYINT(1) | |
| status | ENUM | `email_pending`, `confirmed`, `cancelled` |
| reservation_date | DATETIME | DEFAULT CURRENT_TIMESTAMP |

**admin_users** — id, username (UNIQUE), password (hash), email, last_login, created_at

**newsletter_subscribers** — id, email (UNIQUE), confirmation_token, is_confirmed, subscribe_date, confirm_date, ip_address

### Conexión

Archivo `includes/db_config.php` detecta el entorno automáticamente:
- Si `HTTP_HOST` contiene `cangrejosalbinos.com` → carga `.env.production`
- Si no → carga `.env.local`

Usa **mysqli** para reservas y admin. El newsletter usa **PDO**.

## Configuración del evento

Archivo `includes/event_config.php`:
```php
define('EVENTO_CAPACIDAD_MAXIMA', 450);
define('EVENTO_MAXIMO_POR_PERSONA', 4);
```

Datos del evento hardcodeados en templates de email:
- **Fecha:** 17 de mayo de 2025
- **Hora:** 20:30
- **Lugar:** Jameos del Agua, Lanzarote

## Flujo de reservas

```
1. Usuario → index.php (formulario con CSRF token)
2. AJAX POST → process_reservation.php
   - Validación servidor (campos, email, teléfono, capacidad, duplicados)
   - INSERT con status='email_pending'
   - Genera confirmation_code (MD5) y confirmation_token
   - Envía email de verificación
3. Usuario click email → confirm_reservation.php?token=xxx
   - UPDATE status='confirmed', email_confirmed=1
   - Envía email de confirmación con detalles del evento
   - Notifica al admin
4. (Opcional) Cancel → cancel_reservation.php?code=xxx&email=xxx
   - UPDATE status='cancelled'
   - Envía email de cancelación + notifica admin
   - Entradas se liberan automáticamente
```

## Sistema de emails

5 tipos definidos como constantes en `includes/email/email_template.php`:

| Constante | Propósito |
|---|---|
| `EMAIL_TYPE_VERIFICATION` | Confirmar email tras reserva |
| `EMAIL_TYPE_CONFIRMATION` | Reserva confirmada (incluye info de acceso) |
| `EMAIL_TYPE_CANCELLATION` | Reserva cancelada |
| `EMAIL_TYPE_ADMIN_NOTIFICATION` | Notificación a admin (new_reservation / cancellation) |
| `EMAIL_TYPE_NEWSLETTER_CONFIRMATION` | Confirmar suscripción newsletter |

**Funciones clave:**
- `generate_email($type, $data, $baseURL)` → retorna `['subject', 'body']`
- `send_template_email($to, $type, $data, $baseURL)` → genera + envía
- `send_email_phpmailer($to, $subject, $body, ...)` → wrapper PHPMailer

**SMTP:** smtp.hostinger.com:465 (SSL), from: no-reply@cangrejosalbinos.com

## Panel Admin

- **Login:** username/password con `password_verify()`, CSRF, session regeneration
- **Dashboard:** Tabla paginada (10/página), filtros por estado, búsqueda por nombre/email
- **Acciones:** Cambiar estado de reservas, cancelar (envía email al usuario)
- **Export:** CSV con BOM UTF-8
- **Stats:** Entradas totales/confirmadas/pendientes/canceladas/disponibles

**Credenciales por defecto:** admin / change_me_immediately

## Seguridad

- CSRF tokens en todos los formularios
- `password_hash()` / `password_verify()` para admin
- Prepared statements (mysqli)
- Sanitización de input (`htmlspecialchars`, `trim`)
- `.htaccess`: bloqueo de archivos sensibles, headers de seguridad (XSS, MIME, Frame)
- Session regeneration en login

## Desarrollo local

### Requisitos
- PHP 7.2+, MySQL/MariaDB, Apache (MAMP Pro)
- Extensiones PHP: mysqli

### Setup
1. Crear `.env.local` en la raíz:
   ```
   APP_ENV=local
   DB_HOST=127.0.0.1
   DB_NAME=tu_base_de_datos
   DB_USER=root
   DB_PASS=root
   ```
2. `composer install`
3. Crear tablas en MySQL (esquema arriba)
4. Acceder via MAMP: http://localhost:8888/cangrejos_albinos_26/

### Scripts de setup (gitignored, solo local)
- `setup_database.php` — Crea tablas + admin inicial
- `update_database.php` — Migraciones de esquema
- `update_db_password.php` — Actualizar credenciales BD
- `admin/check_users.php` — Verificar/crear admin users
- `test_system.php`, `test_email.php`, `test_email_template.php` — Tests

## Notas para desarrollo

- Al modificar la capacidad o entradas por persona, editar `includes/event_config.php`
- Al cambiar fecha/hora/lugar del evento, editar `includes/email/email_template.php` (hardcodeado en 4 secciones)
- El formulario de reserva está embebido directamente en `index.php`
- Newsletter usa PDO (`process_newsletter.php`), el resto usa mysqli
- Zona horaria: Europe/Madrid
- Los archivos HTML de eventos (`entradas-*.html`) son páginas independientes con formularios embebidos
