<?php
// Start session
session_start();

// Debug session info (quitar en producción)
$session_data = [];
foreach ($_SESSION as $key => $value) {
    if (is_scalar($value)) {  // Solo almacenar valores simples, no arrays ni objetos
        $session_data[$key] = $value;
    } else {
        $session_data[$key] = gettype($value);
    }
}
error_log("Sesión actual en dashboard.php: " . json_encode($session_data));

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, redirect to login page
    error_log("Usuario no está logueado, redirigiendo a index.php");
    header('Location: index.php');
    exit;
}

// Include database configuration
require_once '../includes/db_config.php';
require_once __DIR__ . '/../includes/event_config.php';

// Set default pagination variables
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($current_page - 1) * $records_per_page;

// Set default filter and search parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the base SQL query
$sql = "SELECT * FROM reservations WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM reservations WHERE 1=1";

// Add filter and search conditions if provided
$params = [];
$param_types = '';

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $count_sql .= " AND status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($search_term)) {
    $sql .= " AND (name LIKE ? OR last_name LIKE ? OR email LIKE ? LIKE ? OR confirmation_code LIKE ?)";
    $count_sql .= " AND (name LIKE ? OR last_name LIKE ? OR email LIKE ? LIKE ? OR confirmation_code LIKE ?)";
    $search_pattern = "%$search_term%";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $param_types .= 'sssss';
}

// Finalize the SQL query with ordering and pagination
$sql .= " ORDER BY reservation_date DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $records_per_page;
$param_types .= 'ii';

// Get total number of records (for pagination)
$count_stmt = $conn->prepare($count_sql);

if (!empty($params) && !empty($param_types)) {
    // Remove the last two parameters (offset and limit) for the count query
    $count_params = array_slice($params, 0, -2);
    $count_param_types = substr($param_types, 0, -2);
    
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_param_types, ...$count_params);
    }
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_records = isset($count_row['total']) ? $count_row['total'] : 0;
$total_pages = $total_records > 0 ? ceil($total_records / $records_per_page) : 1;

// Get the reservations for the current page
$stmt = $conn->prepare($sql);

if (!empty($params) && !empty($param_types)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$reservations = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Get total tickets count (excluyendo canceladas)
$tickets_sql = "SELECT SUM(num_tickets) as total_tickets FROM reservations WHERE status != 'cancelled'";
$tickets_result = $conn->query($tickets_sql);
$tickets_row = $tickets_result->fetch_assoc();
$total_tickets = isset($tickets_row['total_tickets']) ? $tickets_row['total_tickets'] : 0;


//calcula reservas confirmadas
$sql_confirmadas = "SELECT COUNT(*) as total FROM reservations WHERE status != 'cancelled'";
$result_confirmadas = $conn->query($sql_confirmadas);
$row_confirmadas = $result_confirmadas->fetch_assoc();
$reservas_confirmadas = isset($row_confirmadas['total']) ? $row_confirmadas['total'] : 0;

// Define total capacity
$total_capacity = EVENTO_CAPACIDAD_MAXIMA;
$tickets_available = $total_capacity - $total_tickets;

// Handle reservation status update
$status_update_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_POST['reservation_id']) && isset($_POST['new_status'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $status_update_msg = '<div class="alert alert-danger">Error de seguridad. Por favor, intenta nuevamente.</div>';
    } else {
        $reservation_id = (int)$_POST['reservation_id'];
        $new_status = $_POST['new_status'];
        
        // Validate status
        $valid_statuses = ['email_pending', 'confirmed', 'cancelled'];
        if (in_array($new_status, $valid_statuses)) {
            $update_sql = "UPDATE reservations SET status = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_status, $reservation_id);
            
            if ($update_stmt->execute()) {
                $status_update_msg = '<div class="alert alert-success">Estado actualizado correctamente.</div>';
                
                // Si la reserva se cancela, enviar notificación al usuario
                if ($new_status === 'cancelled') {
                    // Obtener datos de la reserva
                    $get_reservation_sql = "SELECT * FROM reservations WHERE id = ?";
                    $get_stmt = $conn->prepare($get_reservation_sql);
                    $get_stmt->bind_param("i", $reservation_id);
                    $get_stmt->execute();
                    $reservation_result = $get_stmt->get_result();
                    
                    if ($reservation = $reservation_result->fetch_assoc()) {
                        // Incluir el sistema de plantillas de email
                        if (file_exists('../includes/email/email_template.php')) {
                            require_once '../includes/email/email_template.php';
                        }
                        
                        // Generate base URL
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                        $baseURL = $protocol . "://" . $host;
                        
                        // Preparar datos para el email de cancelación
                        $cancel_data = [
                            'id' => $reservation['id'],
                            'name' => $reservation['name'],
                            'last_name' => $reservation['last_name'],
                            'email' => $reservation['email'],
                            'phone' => $reservation['phone'],
                            'confirmation_code' => $reservation['confirmation_code'],
                            'num_tickets' => $reservation['num_tickets'],
                            'reservation_date' => $reservation['reservation_date']
                        ];
                        
                        // Enviar email de cancelación usando el sistema de plantilla unificada
                        if (function_exists('send_template_email')) {
                            send_template_email($reservation['email'], EMAIL_TYPE_CANCELLATION, $cancel_data, $baseURL);
                        } else {
                            // Fallback en caso de que no se pueda cargar el sistema de plantillas
                            $cancel_email = $reservation['email'];
                            $cancel_subject = "Cancelación de reserva - Cangrejos Albinos";
                            
                            $cancel_message = "
                            <html>
                            <head>
                                <title>Cancelación de Reserva</title>
                            </head>
                            <body>
                                <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
                                    <div style='text-align: center; margin-bottom: 20px;'>
                                        <img src='https://cangrejosalbinos.com/assets/img/email/CACT_logotipo.png' alt='CACT Lanzarote' style='max-width: 200px;'>
                                    </div>
                                    
                                    <h2 style='color: #dc3545; text-align: center;'>Reserva Cancelada</h2>
                                    
                                    <p>Estimado/a <strong>{$reservation['name']} {$reservation['last_name']}</strong>,</p>
                                    
                                    <p>Lamentamos informarte que tu reserva para el evento \"Cangrejos Albinos\" ha sido cancelada.</p>
                                    
                                    <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                                        <h3 style='margin-top: 0; color: #333;'>Detalles de la reserva cancelada:</h3>
                                        <ul style='padding-left: 20px;'>
                                            <li><strong>Número de confirmación:</strong> {$reservation['confirmation_code']}</li>
                                            <li><strong>Número de entradas:</strong> {$reservation['num_tickets']}</li>
                                            <li><strong>Fecha del evento:</strong> 17 de mayo de 2025</li>
                                        </ul>
                                    </div>
                                    
                                    <p>Si tiene alguna duda, póngase en contacto con nosotros a través de <a href=\"mailto:info@centrosturisticos.com\">info@centrosturisticos.com</a></p>
                                    
                                    <p>Sentimos las molestias que esto pueda ocasionarte.</p>
                                    
                                    <p>Atentamente,<br>El equipo de CACT Lanzarote</p>
                                </div>
                            </body>
                            </html>";
                            
                            // Headers
                            $cancel_headers = "MIME-Version: 1.0" . "\r\n";
                            $cancel_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                            $cancel_headers .= "From: Cangrejos Albinos <noreply@cactlanzarote.com>" . "\r\n";
                            
                            // Enviar email de cancelación
                            mail($cancel_email, $cancel_subject, $cancel_message, $cancel_headers);
                        }
                        
                        // Log de la acción
                        error_log("Reserva #{$reservation_id} cancelada por el administrador " . $_SESSION['admin_username'] . ". Notificación enviada a {$cancel_email}");
                    }
                    
                    $get_stmt->close();
                }
                
                // Refresh the page to show updated data
                header("Location: dashboard.php?status=$status_filter&search=$search_term&page=$current_page&updated=1&new_status=$new_status");
                exit;
            } else {
                $status_update_msg = '<div class="alert alert-danger">Error al actualizar el estado.</div>';
            }
            
            $update_stmt->close();
        } else {
            $status_update_msg = '<div class="alert alert-danger">Estado no válido.</div>';
        }
    }
}

// Show success message if status was updated
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $status_message = "Estado actualizado correctamente.";
    
    if (isset($_GET['new_status'])) {
        switch ($_GET['new_status']) {
            case 'cancelled':
                $status_message = "Reserva cancelada correctamente. Las entradas han sido liberadas y se ha enviado un email al usuario.";
                break;
            case 'confirmed':
                $status_message = "Reserva confirmada correctamente.";
                break;
            case 'email_pending':
                $status_message = "Estado actualizado a 'Email Pendiente'.";
                break;
        }
    }
    
    $status_update_msg = '<div class="alert alert-success">' . $status_message . '</div>';
}

// Generate new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Close statement and result
$stmt->close();
$count_stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | Cangrejos Albinos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .header {
            margin-bottom: 30px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .card-title {
            margin-bottom: 0;
        }
        .status-badge {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .badge-warning {
            background-color: #ff9800;
            color: #212529;
        }
        .badge-confirmed {
            background-color: #28a745;
        }
        .badge-cancelled {
            background-color: #dc3545;
        }
        .actions .btn {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header d-flex justify-content-between align-items-center">
            <h1>Panel de Administración - Reservas</h1>
            <div>
                <span class="mr-3">Bienvenido, <?php 
                    if (isset($_SESSION['admin_username']) && !empty($_SESSION['admin_username'])) {
                        echo htmlspecialchars($_SESSION['admin_username']);
                    } elseif (isset($_SESSION['admin_id'])) {
                        echo "Admin #" . $_SESSION['admin_id'];
                    } else {
                        echo "Administrador";
                    }
                ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>
        
        <?php if (!empty($status_update_msg)) : ?>
            <?php echo $status_update_msg; ?>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total de Reservas (Confirmadas)</h5>
                        <h2 class="text-primary"><?php echo $total_records; ?> (<?php echo $reservas_confirmadas; ?>)</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total de Entradas</h5>
                        <h2 class="text-success"><?php echo $total_tickets; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Entradas Disponibles</h5>
                        <h2 class="text-info"><?php echo $tickets_available; ?></h2>
                        <a href="reservas_stats.php" class="btn btn-primary btn-sm mt-2">Ver Estadísticas Detalladas</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title">Filtrar Reservas</h5>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="export.php" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Exportar a Excel</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="dashboard.php" method="get" class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="search">Buscar</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Nombre, email o código" value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmado</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Lista de Reservas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Entradas</th>
                                <th>Fecha (UTC)</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservations)) : ?>
                                <tr>
                                    <td colspan="9" class="text-center">No se encontraron reservas.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($reservations as $reservation) : ?>
                                    <tr>
                                        <td><?php echo $reservation['id']; ?></td>
                                        <td><?php echo htmlspecialchars($reservation['name'] . ' ' . $reservation['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['email']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['phone']); ?></td>
                                        <td><?php echo $reservation['num_tickets']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($reservation['reservation_date'])); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch ($reservation['status']) {
                                                case 'email_pending':
                                                    $status_class = 'badge-warning';
                                                    $status_text = 'Email Pendiente';
                                                    break;
                                                case 'confirmed':
                                                    $status_class = 'badge-confirmed';
                                                    $status_text = 'Confirmado';
                                                    break;
                                                case 'cancelled':
                                                    $status_class = 'badge-cancelled';
                                                    $status_text = 'Cancelado';
                                                    break;
                                                default:
                                                    $status_class = 'badge-secondary';
                                                    $status_text = $reservation['status'];
                                            }
                                            ?>
                                            <span class="badge status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td class="actions">
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailsModal<?php echo $reservation['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#statusModal<?php echo $reservation['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Details Modal -->
                                    <div class="modal fade" id="detailsModal<?php echo $reservation['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel<?php echo $reservation['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="detailsModalLabel<?php echo $reservation['id']; ?>">Detalles de la Reserva</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <dl class="row">
                                                        <dt class="col-sm-4">ID de Reserva:</dt>
                                                        <dd class="col-sm-8"><?php echo $reservation['id']; ?></dd>
                                                        
                                                        <dt class="col-sm-4">Código:</dt>
                                                        <dd class="col-sm-8"><?php echo $reservation['confirmation_code']; ?></dd>
                                                        
                                                        <dt class="col-sm-4">Nombre:</dt>
                                                        <dd class="col-sm-8"><?php echo htmlspecialchars($reservation['name']); ?></dd>
                                                        
                                                        <dt class="col-sm-4">Apellidos:</dt>
                                                        <dd class="col-sm-8"><?php echo htmlspecialchars($reservation['last_name']); ?></dd>
                                                        
                                                        <dt class="col-sm-4">Email:</dt>
                                                        <dd class="col-sm-8"><?php echo htmlspecialchars($reservation['email']); ?></dd>
                                                        
                                                        <dt class="col-sm-4">Teléfono:</dt>
                                                        <dd class="col-sm-8"><?php echo htmlspecialchars($reservation['phone']); ?></dd>
                                                        
                                                        <dt class="col-sm-4">Num. Entradas:</dt>
                                                        <dd class="col-sm-8"><?php echo $reservation['num_tickets']; ?></dd>
                                                        
                                                        <dt class="col-sm-4">Fecha de Reserva (UTC):</dt>
                                                        <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($reservation['reservation_date'])); ?></dd>
                                                        
                                                        <dt class="col-sm-4">Estado:</dt>
                                                        <dd class="col-sm-8"><span class="badge status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></dd>
                                                        
                                                        <dt class="col-sm-4">Comentarios:</dt>
                                                        <dd class="col-sm-8">
                                                            <?php echo !empty($reservation['comments']) ? htmlspecialchars($reservation['comments']) : 'No hay comentarios'; ?>
                                                        </dd>
                                                    </dl>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $reservation['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel<?php echo $reservation['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="statusModalLabel<?php echo $reservation['id']; ?>">Actualizar Estado</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="dashboard.php" method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                        <input type="hidden" name="update_status" value="1">
                                                        
                                                        <div class="form-group">
                                                            <label for="new_status<?php echo $reservation['id']; ?>">Nuevo Estado</label>
                                                            <select class="form-control" id="new_status<?php echo $reservation['id']; ?>" name="new_status">
                                                                <option value="email_pending" <?php echo $reservation['status'] === 'email_pending' ? 'selected' : ''; ?>>Email Pendiente</option>
                                                                <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmado</option>
                                                                <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total_pages > 1) : ?>
                <div class="card-footer">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($current_page > 1) : ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_term); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_term); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages) : ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search_term); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>