<?php
// Verificación de inicio de sesión del administrador
session_start();

// Verificar si el usuario está logueado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Incluir configuración de base de datos
require_once '../includes/db_config.php';
require_once __DIR__ . '/../includes/event_config.php';

// Obtener las estadísticas de reservas
$stats = [];

// Leer el total de entradas disponibles desde la configuración centralizada
$total_tickets = EVENTO_CAPACIDAD_MAXIMA;

// Contar reservas por estado
$status_query = "SELECT status, COUNT(*) as count, SUM(num_tickets) as tickets FROM reservations GROUP BY status";
$status_result = $conn->query($status_query);

$total_reservations = 0;
$total_tickets_reserved = 0;
$total_tickets_confirmed = 0;
$total_tickets_cancelled = 0;
$total_tickets_pending = 0;

if ($status_result) {
    while ($row = $status_result->fetch_assoc()) {
        $stats['status'][$row['status']] = [
            'count' => $row['count'],
            'tickets' => $row['tickets']
        ];
        
        $total_reservations += $row['count'];
        
        if ($row['status'] == 'confirmed') {
            $total_tickets_confirmed += $row['tickets'];
        } elseif ($row['status'] == 'cancelled') {
            $total_tickets_cancelled += $row['tickets'];
        } elseif ($row['status'] == 'email_pending') {
            $total_tickets_pending += $row['tickets'];
        }
        
        if ($row['status'] != 'cancelled') {
            $total_tickets_reserved += $row['tickets'];
        }
    }
}

// Tickets disponibles
$tickets_available = $total_tickets - $total_tickets_reserved;

// Obtener las reservas más recientes
$recent_query = "SELECT * FROM reservations ORDER BY reservation_date DESC LIMIT 5";
$recent_result = $conn->query($recent_query);
$recent_reservations = [];

if ($recent_result) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_reservations[] = $row;
    }
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Reservas - Panel de Administración</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.min.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .stats-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-box {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        .ticket-available {
            color: #28a745;
        }
        .ticket-reserved {
            color: #007bff;
        }
        .ticket-cancelled {
            color: #dc3545;
        }
        .ticket-pending {
            color: #ffc107;
        }
        .progress {
            height: 25px;
            margin: 15px 0;
        }
        .recent-table {
            margin-top: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        h1, h2, h3 {
            color: #343a40;
        }
        .header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="stats-container">
        <div class="header">
            <h1>Estadísticas de Reservas</h1>
            <a href="dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="stats-card">
                    <h3>Estado General de Entradas</h3>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value ticket-available"><?php echo $tickets_available; ?></div>
                                <div class="stat-label">Entradas Disponibles</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value ticket-reserved"><?php echo $total_tickets_confirmed; ?></div>
                                <div class="stat-label">Entradas Confirmadas</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value ticket-pending"><?php echo $total_tickets_pending; ?></div>
                                <div class="stat-label">Entradas Pendientes</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value ticket-cancelled"><?php echo $total_tickets_cancelled; ?></div>
                                <div class="stat-label">Entradas Canceladas</div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4">Distribución de Entradas</h5>
                    <div class="progress">
                        <?php 
                        $confirmed_percent = ($total_tickets_confirmed / $total_tickets) * 100;
                        $pending_percent = ($total_tickets_pending / $total_tickets) * 100;
                        $available_percent = ($tickets_available / $total_tickets) * 100;
                        ?>
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $confirmed_percent; ?>%" 
                             aria-valuenow="<?php echo $confirmed_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo round($confirmed_percent); ?>%
                        </div>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pending_percent; ?>%" 
                             aria-valuenow="<?php echo $pending_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo round($pending_percent); ?>%
                        </div>
                        <div class="progress-bar bg-light text-dark" role="progressbar" style="width: <?php echo $available_percent; ?>%" 
                             aria-valuenow="<?php echo $available_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo round($available_percent); ?>%
                        </div>
                    </div>
                    <div class="small text-muted mt-2">
                        <span class="badge bg-success"></span> Confirmadas
                        <span class="badge bg-warning ml-2"></span> Pendientes
                        <span class="badge bg-light text-dark ml-2"></span> Disponibles
                    </div>
                    
                    <h5 class="mt-4">Resumen de Entradas</h5>
                    <table class="table table-bordered table-striped mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th>Total de Entradas</th>
                                <th>Entradas Confirmadas</th>
                                <th>Entradas Pendientes</th>
                                <th>Entradas Canceladas</th>
                                <th>Entradas Disponibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $total_tickets; ?></td>
                                <td><?php echo $total_tickets_confirmed; ?> (<?php echo round(($total_tickets_confirmed / $total_tickets) * 100); ?>%)</td>
                                <td><?php echo $total_tickets_pending; ?> (<?php echo round(($total_tickets_pending / $total_tickets) * 100); ?>%)</td>
                                <td><?php echo $total_tickets_cancelled; ?> (<?php echo round(($total_tickets_cancelled / $total_tickets) * 100); ?>%)</td>
                                <td><?php echo $tickets_available; ?> (<?php echo round(($tickets_available / $total_tickets) * 100); ?>%)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="stats-card">
                    <h3>Reservas Recientes</h3>
                    <table class="table table-hover recent-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Entradas</th>
                                <th>Estado</th>
                                <th>Fecha de Reserva (UTC)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_reservations as $reservation): ?>
                            <tr>
                                <td><?php echo $reservation['id']; ?></td>
                                <td><?php echo htmlspecialchars($reservation['name'] . ' ' . $reservation['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['email']); ?></td>
                                <td><?php echo $reservation['num_tickets']; ?></td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch ($reservation['status']) {
                                        case 'confirmed':
                                            $status_class = 'status-confirmed';
                                            $status_text = 'Confirmada';
                                            break;
                                        case 'email_pending':
                                            $status_class = 'status-pending';
                                            $status_text = 'Pendiente de Email';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'status-cancelled';
                                            $status_text = 'Cancelada';
                                            break;
                                        default:
                                            $status_class = '';
                                            $status_text = $reservation['status'];
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($reservation['reservation_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_reservations)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay reservas recientes</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <a href="dashboard.php" class="btn btn-primary">Ver todas las reservas</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/vendor/jquery.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>