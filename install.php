<?php
$base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación del Sistema - Cangrejos Albinos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .btn-group {
            width: 100%;
        }
        .btn {
            margin-right: 5px;
        }
        h1 {
            margin-bottom: 30px;
            text-align: center;
        }
        .step {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #007bff;
            color: white;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        .filename {
            font-family: monospace;
            background-color: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .checklist-item {
            margin-bottom: 10px;
        }
        .checklist-item.completed {
            color: #28a745;
        }
        .checklist-item.todo {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <h1>Instalación del Sistema de Reservas<br><small class="text-muted">Cangrejos Albinos</small></h1>
    
    <div class="alert alert-warning">
        <strong>¡Importante!</strong> Este archivo de instalación debe eliminarse después de completar la configuración en producción por razones de seguridad.
    </div>
    
    <h2 class="section-title">Guía de Instalación</h2>
    
    <div class="card">
        <div class="card-header">1. Requisitos Previos</div>
        <div class="card-body">
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    PHP 7.2 o superior
                    <?php echo version_compare(PHP_VERSION, '7.2.0') >= 0 ? 
                        '<span class="badge badge-success">✓ ' . PHP_VERSION . '</span>' : 
                        '<span class="badge badge-danger">✗ ' . PHP_VERSION . '</span>'; ?>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Extensión MySQLi
                    <?php echo extension_loaded('mysqli') ? 
                        '<span class="badge badge-success">✓ Disponible</span>' : 
                        '<span class="badge badge-danger">✗ No disponible</span>'; ?>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Permisos de escritura en directorio raíz
                    <?php echo is_writable('.') ? 
                        '<span class="badge badge-success">✓ Disponible</span>' : 
                        '<span class="badge badge-danger">✗ No disponible</span>'; ?>
                </li>
            </ul>
            
            <p>Asegúrate de tener una base de datos MySQL disponible y credenciales con permisos para crear tablas.</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">2. Configuración de la Base de Datos</div>
        <div class="card-body">
            <p>Sigue estos pasos para configurar la base de datos:</p>
            <ol>
                <li class="mb-2">
                    <a href="update_db_password.php" class="btn btn-secondary btn-sm" target="_blank">Configurar credenciales de MySQL</a>
                    <small class="text-muted d-block mt-1">Configura usuario, contraseña y otros parámetros de conexión a MySQL</small>
                </li>
                <li class="mb-2">
                    <a href="setup_database.php" class="btn btn-primary btn-sm" target="_blank">Configurar Base de Datos</a>
                    <small class="text-muted d-block mt-1">Crea la base de datos, tablas y el usuario administrador</small>
                </li>
                <li>
                    <a href="admin/check_users.php" class="btn btn-warning btn-sm" target="_blank">Verificar usuarios admin</a>
                    <small class="text-muted d-block mt-1">Verifica que el usuario administrador está creado correctamente</small>
                </li>
            </ol>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">3. Verificación del Sistema</div>
        <div class="card-body">
            <div class="btn-group-vertical w-100">
                <a href="index.php#entradas" class="btn btn-success mb-2" target="_blank">Formulario de Reserva</a>
                <a href="admin/" class="btn btn-danger" target="_blank">Panel de Administración</a>
            </div>
            <div class="mt-3">
                <small><strong>Credenciales de administrador:</strong> Usuario: admin | Contraseña: change_me_immediately</small>
                <div class="alert alert-warning mt-2">
                    <small>¡Cambia la contraseña del administrador inmediatamente después de acceder!</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">4. Pruebas del Sistema</div>
        <div class="card-body">
            <p>Realiza las siguientes pruebas para garantizar que todo funciona correctamente:</p>
            <div class="step">
                <span class="step-number">1</span>
                <span>Realiza una reserva de prueba en el formulario.</span>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <span>Verifica que el email de confirmación se envía correctamente.</span>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <span>Confirma la reserva haciendo clic en el enlace del email.</span>
            </div>
            <div class="step">
                <span class="step-number">4</span>
                <span>Accede al panel de administrador y verifica que la reserva aparece como confirmada.</span>
            </div>
            <div class="step">
                <span class="step-number">5</span>
                <span>Prueba la funcionalidad de exportación de datos en el panel de administración.</span>
            </div>
            <div class="step">
                <span class="step-number">6</span>
                <span>Prueba a cancelar una reserva desde el enlace en el email de confirmación.</span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">5. Configuración de Producción</div>
        <div class="card-body">
            <p>Antes de poner el sistema en producción, realiza las siguientes tareas:</p>
            
            <div class="checklist-item todo">
                <input type="checkbox" id="task1"> 
                <label for="task1">Cambia la contraseña del usuario administrador a una contraseña segura</label>
            </div>
            
            <div class="checklist-item todo">
                <input type="checkbox" id="task2"> 
                <label for="task2">Configura el email del administrador en <span class="filename">includes/email/email_template.php</span> (busca <code>admin@cangrejos-albinos.com</code>)</label>
            </div>
            
            <div class="checklist-item todo">
                <input type="checkbox" id="task3"> 
                <label for="task3">Verifica y ajusta el número total de entradas disponibles en <span class="filename">index.php</span> y <span class="filename">process_reservation.php</span></label>
            </div>
            
            <div class="checklist-item todo">
                <input type="checkbox" id="task4"> 
                <label for="task4">Revisa y actualiza cualquier texto específico en los mensajes de email y confirmaciones</label>
            </div>
            
            <div class="checklist-item todo">
                <input type="checkbox" id="task5"> 
                <label for="task5">Configura un archivo <span class="filename">.htaccess</span> para protección adicional</label>
            </div>
            
            <div class="checklist-item todo">
                <input type="checkbox" id="task6"> 
                <label for="task6">Elimina los archivos de instalación una vez completada la configuración</label>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">6. Archivos del Sistema</div>
        <div class="card-body">
            <h5>Archivos Principales:</h5>
            <ul class="list-group mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    index.php
                    <span class="badge badge-primary badge-pill">Página principal</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    index_form.php
                    <span class="badge badge-primary badge-pill">Formulario de reserva</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    process_reservation.php
                    <span class="badge badge-primary badge-pill">Procesamiento de reservas</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    confirm_reservation.php
                    <span class="badge badge-primary badge-pill">Confirmación de reservas</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    cancel_reservation.php
                    <span class="badge badge-primary badge-pill">Cancelación de reservas</span>
                </li>
            </ul>
            
            <h5>Panel de Administración:</h5>
            <ul class="list-group mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    admin/index.php
                    <span class="badge badge-danger badge-pill">Login de administración</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    admin/dashboard.php
                    <span class="badge badge-danger badge-pill">Panel de administración</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    admin/export.php
                    <span class="badge badge-danger badge-pill">Exportación de datos</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    admin/reservas_stats.php
                    <span class="badge badge-danger badge-pill">Estadísticas de reservas</span>
                </li>
            </ul>
            
            <h5>Configuración:</h5>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    includes/db_config.php
                    <span class="badge badge-info badge-pill">Configuración de BD</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    includes/email/email_template.php
                    <span class="badge badge-info badge-pill">Plantillas de email</span>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">7. Archivos de Instalación a Eliminar</div>
        <div class="card-body">
            <p>Por seguridad, elimina los siguientes archivos una vez que el sistema esté configurado correctamente:</p>
            <ul class="list-group">
                <li class="list-group-item list-group-item-warning">install.php (este archivo)</li>
                <li class="list-group-item list-group-item-warning">setup_database.php</li>
                <li class="list-group-item list-group-item-warning">update_database.php</li>
                <li class="list-group-item list-group-item-warning">update_db_password.php</li>
                <li class="list-group-item list-group-item-warning">database.sql</li>
                <li class="list-group-item list-group-item-warning">database_update.sql</li>
                <li class="list-group-item list-group-item-warning">database_update_direct.sql</li>
                <li class="list-group-item list-group-item-warning">admin/check_users.php</li>
                <li class="list-group-item list-group-item-warning">admin/debug_login.php</li>
            </ul>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">8. Integración con index.php</div>
        <div class="card-body">
            <p>Una vez que hayas verificado que todo funciona correctamente, puedes integrar el formulario en tu archivo index.php principal:</p>
            <ol>
                <li>Copia el contenido del div con ID "entradas" desde index_form.php</li>
                <li>Reemplaza la sección correspondiente en index.php</li>
                <li>Asegúrate de integrar también el código PHP del inicio de index_form.php</li>
                <li>Transfiere los estilos CSS necesarios</li>
                <li>Incluye los scripts JavaScript de validación del formulario</li>
            </ol>
            <div class="alert alert-info">
                <small>El formulario ya está diseñado para integrarse fácilmente con el diseño existente de index.php.</small>
            </div>
        </div>
    </div>
    
    <footer class="mt-5 text-center text-muted">
        <small>Sistema de Reservas - Cangrejos Albinos © <?php echo date('Y'); ?></small>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Simple script to save checkbox states
        $(document).ready(function() {
            // Load saved states
            $('.checklist-item input[type="checkbox"]').each(function() {
                var id = $(this).attr('id');
                if (localStorage.getItem(id) === 'true') {
                    $(this).prop('checked', true);
                    $(this).parent().removeClass('todo').addClass('completed');
                }
            });
            
            // Save state on change
            $('.checklist-item input[type="checkbox"]').change(function() {
                var id = $(this).attr('id');
                localStorage.setItem(id, $(this).prop('checked'));
                
                if ($(this).prop('checked')) {
                    $(this).parent().removeClass('todo').addClass('completed');
                } else {
                    $(this).parent().removeClass('completed').addClass('todo');
                }
            });
        });
    </script>
</body>
</html>