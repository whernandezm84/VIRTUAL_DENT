<?php
require_once '../includes/check_auth.php';
require_once '../includes/functions.php';

$db = db_connect();
$user_id = $_SESSION['user_id'];

// Obtener estadísticas del cliente
$stats = [
    'pending' => $db->query("SELECT COUNT(*) FROM requests WHERE user_id = $user_id AND status = 'pending'")->fetchColumn(),
    'in_progress' => $db->query("SELECT COUNT(*) FROM requests WHERE user_id = $user_id AND status = 'in_progress'")->fetchColumn(),
    'completed' => $db->query("SELECT COUNT(*) FROM requests WHERE user_id = $user_id AND status = 'completed'")->fetchColumn()
];

// Obtener solicitudes recientes
$recent_requests = $db->query(
    "SELECT r.id, s.name as service_name, r.status, r.created_at
     FROM requests r
     JOIN services s ON r.service_id = s.id
     WHERE r.user_id = $user_id
     ORDER BY r.created_at DESC
     LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mi Panel - DentalTech</title>
    <link rel="stylesheet" href="../assets/css/client.css">
</head>
<body>
    <?php include '../includes/client_header.php'; ?>

    <div class="container">
        <h1>Bienvenido/a, <?= htmlspecialchars($_SESSION['name']) ?></h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['pending'] ?></span>
                    <span class="stat-label">Pendientes</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['in_progress'] ?></span>
                    <span class="stat-label">En Proceso</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['completed'] ?></span>
                    <span class="stat-label">Completadas</span>
                </div>
            </div>
        </div>

        <div class="dashboard-sections">
            <section class="requests-section">
                <h2>Mis Solicitudes Recientes</h2>

                <?php if(count($recent_requests) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_requests as $request): ?>
                            <tr>
                                <td><?= htmlspecialchars($request['service_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($request['created_at'])) ?></td>
                                <td><span class="status-badge <?= $request['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                </span></td>
                                <td>
                                    <a href="request_detail.php?id=<?= $request['id'] ?>">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="text-right">
                        <a href="requests.php" class="btn">Ver todas</a>
                    </div>
                <?php else: ?>
                    <p>Aún no has realizado ninguna solicitud.</p>
                    <a href="new_request.php" class="btn">Crear primera solicitud</a>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php include '../includes/client_footer.php'; ?>
</body>
</html>
