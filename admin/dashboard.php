<?php
require_once '../includes/check_admin.php';
require_once '../includes/functions.php';

$db = db_connect();

// Estadísticas principales
$stats = [];
$stats['total_users'] = mysqli_query($db, "SELECT COUNT(*) FROM users")->fetch_row()[0];
$stats['active_requests'] = mysqli_query($db, "SELECT COUNT(*) FROM requests WHERE status IN ('pending','in_progress')")->fetch_row()[0];
$stats['completed_requests'] = mysqli_query($db, "SELECT COUNT(*) FROM requests WHERE status = 'completed'")->fetch_row()[0];

// Solicitudes recientes
$recent_requests = [];
$result = mysqli_query($db,
    "SELECT r.id, u.name as client, s.name as service, r.status, r.created_at
     FROM requests r
     JOIN users u ON r.user_id = u.id
     JOIN services s ON r.service_id = s.id
     ORDER BY r.created_at DESC LIMIT 5");

while($row = mysqli_fetch_assoc($result)) {
    $recent_requests[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/admin_nav.php'; ?>

    <div class="container">
        <h1>Panel de Administración</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Usuarios</h3>
                <p><?= $stats['total_users'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Solicitudes Activas</h3>
                <p><?= $stats['active_requests'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Completadas</h3>
                <p><?= $stats['completed_requests'] ?></p>
            </div>
        </div>

        <h2>Solicitudes Recientes</h2>
        <table class="requests-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_requests as $request): ?>
                <tr>
                    <td><?= $request['id'] ?></td>
                    <td><?= htmlspecialchars($request['client']) ?></td>
                    <td><?= htmlspecialchars($request['service']) ?></td>
                    <td><?= date('d/m/Y', strtotime($request['created_at'])) ?></td>
                    <td><span class="status-badge <?= $request['status'] ?>"><?= $request['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
