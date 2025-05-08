<?php
$base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba del Sistema de Reservas - Cangrejos Albinos</title>
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
    </style>
</head>
<body>
    <h1>Sistema de Reservas - Cangrejos Albinos</h1>
    
    <div class="card">
        <div class="card-header">Configuración de la Base de Datos</div>
        <div class="card-body">
            <p>Sigue estos pasos para configurar la base de datos:</p>
            <ol>
                <li><a href="check_connection.php" class="btn btn-info btn-sm mb-2" target="_blank">Verificar conexión a MySQL</a> - Diagnostica problemas de conexión y configuración</li>
                <li><a href="update_db_password.php" class="btn btn-secondary btn-sm mb-2" target="_blank">Configurar credenciales de MySQL</a> - Si necesitas ajustar los parámetros de conexión a MySQL</li>
                <li><a href="setup_database.php" class="btn btn-primary btn-sm" target="_blank">Configurar Base de Datos</a> - Ejecuta este script para crear la base de datos e importar las tablas</li>
                <li><a href="update_database.php" class="btn btn-primary btn-sm mt-2" target="_blank">Actualizar Base de Datos</a> - Añade soporte para confirmación por email</li>
                <li><a href="admin/check_users.php" class="btn btn-warning btn-sm mt-2" target="_blank">Verificar usuarios admin</a> - Comprueba y crea usuarios administradores</li>
            </ol>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Acceso al Sistema</div>
        <div class="card-body">
            <div class="btn-group">
                <a href="index.php#entradas" class="btn btn-success" target="_blank">Formulario de Reserva</a>
                <a href="admin/" class="btn btn-danger" target="_blank">Panel de Administración</a>
            </div>
            <div class="mt-3">
                <small><strong>Credenciales de administrador:</strong> Usuario: admin | Contraseña: change_me_immediately</small>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Pasos para probar el sistema</div>
        <div class="card-body">
            <div class="step">
                <span class="step-number">1</span>
                <span>Ejecuta "Configurar Base de Datos" para crear la base de datos y las tablas necesarias.</span>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <span>Abre el "Formulario de Reserva" y realiza una reserva de prueba.</span>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <span>Verifica que la validación del formulario funcione correctamente intentando enviarlo con campos vacíos o datos inválidos.</span>
            </div>
            <div class="step">
                <span class="step-number">4</span>
                <span>Una vez realizada una reserva exitosa, accede al "Panel de Administración" para verificar que la reserva se haya registrado.</span>
            </div>
            <div class="step">
                <span class="step-number">5</span>
                <span>En el panel de administración, prueba a cambiar el estado de la reserva, buscar reservas y exportar la información.</span>
            </div>
            <div class="step">
                <span class="step-number">6</span>
                <span>Intenta realizar otra reserva con el mismo email o DNI para comprobar que el sistema detecta duplicados.</span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Archivos del sistema</div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    index_form.php
                    <span class="badge badge-primary badge-pill">Formulario de reserva</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    process_reservation.php
                    <span class="badge badge-primary badge-pill">Procesamiento de reservas</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    admin/index.php
                    <span class="badge badge-primary badge-pill">Login de administración</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    admin/dashboard.php
                    <span class="badge badge-primary badge-pill">Panel de administración</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    admin/export.php
                    <span class="badge badge-primary badge-pill">Exportación de datos</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    includes/db_config.php
                    <span class="badge badge-primary badge-pill">Configuración de BD</span>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Integración con index.php</div>
        <div class="card-body">
            <p>Una vez que hayas verificado que todo funciona correctamente, puedes integrar el formulario en tu archivo index.php principal.</p>
            <p>Has completado con éxito la integración del formulario en el archivo index.php principal.</p>
            <p>El sistema de reservas ahora está completamente integrado en la página principal del sitio.</p>
        </div>
    </div>
    
    <footer class="mt-5 text-center text-muted">
        <small>Sistema de Reservas - Cangrejos Albinos © <?php echo date('Y'); ?></small>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>