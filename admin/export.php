<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, redirect to login page
    header('Location: index.php');
    exit;
}

// Include database configuration
require_once '../includes/db_config.php';

// Set filter parameters (same as in dashboard.php)
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL query
$sql = "SELECT * FROM reservations WHERE 1=1";

// Add filter and search conditions if provided
$params = [];
$param_types = '';

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($search_term)) {
    $sql .= " AND (name LIKE ? OR last_name LIKE ? OR email LIKE ? OR dni LIKE ? OR confirmation_code LIKE ?)";
    $search_pattern = "%$search_term%";
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $params[] = $search_pattern;
    $param_types .= 'sssss';
}

// Order by reservation date
$sql .= " ORDER BY reservation_date DESC";

// Get the reservations
$stmt = $conn->prepare($sql);

if (!empty($params) && !empty($param_types)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$reservations = $result->fetch_all(MYSQLI_ASSOC);

// Generate filename with current date and time
$filename = 'reservas_cangrejos_albinos_' . date('Y-m-d_H-i-s') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel to correctly display Spanish characters
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Set CSV headers
fputcsv($output, [
    'ID',
    'Código de Confirmación',
    'Nombre',
    'Apellidos',
    'Email',
    'Teléfono',
    'DNI/NIE',
    'Número de Entradas',
    'Comentarios',
    'Fecha de Reserva',
    'Estado',
    'IP',
    'Privacidad Aceptada'
]);

// Add data rows
foreach ($reservations as $reservation) {
    // Convert status to Spanish
    $status = '';
    switch ($reservation['status']) {
        case 'pending':
            $status = 'Pendiente';
            break;
        case 'confirmed':
            $status = 'Confirmado';
            break;
        case 'cancelled':
            $status = 'Cancelado';
            break;
        default:
            $status = $reservation['status'];
    }
    
    // Privacy accepted
    $privacy = $reservation['privacy_accepted'] ? 'Sí' : 'No';
    
    // Format date
    $date = date('d/m/Y H:i', strtotime($reservation['reservation_date']));
    
    fputcsv($output, [
        $reservation['id'],
        $reservation['confirmation_code'],
        $reservation['name'],
        $reservation['last_name'],
        $reservation['email'],
        $reservation['phone'],
        $reservation['dni'],
        $reservation['num_tickets'],
        $reservation['comments'],
        $date,
        $status,
        $reservation['ip_address'],
        $privacy
    ]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>