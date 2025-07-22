<?php
require_once '../includes/check_auth.php';
require_once '../includes/functions.php';

$db = db_connect();
$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

// Construir consulta según filtro
$query = "SELECT r.*, s.name as service_name
          FROM requests r
          JOIN services s ON r.service_id = s.id
          WHERE r.user_id = ?";

$params = [$user_id];

switch ($filter) {
    case 'active':
        $query .= " AND r.status IN ('pending', 'in_progress')";
        break;
    case 'completed':
        $query .= " AND r.status = 'completed'";
        break;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mis Solicitudes</title>
    <link rel="stylesheet" href="../assets/css/client.css">
</head>
<body>
    <?php include '../includes/client_header.php'; ?>

    <div class="container">
        <h1>Mis Solicitudes</h1>

        <div class="filters">
            <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">Todas</a>
            <a href="?filter=active" class="<?= $filter === 'active' ? 'active' : '' ?>">Activas</a>
            <a href="?filter=completed" class="<?= $filter === 'completed' ? 'active' : '' ?>">Completadas</a>
        </div>

        <div class="requests-list">
            <?php foreach ($requests as $request): ?>
            <div class="request-card">
                <div class="request-header">
                    <h3><?= htmlspecialchars($request['service_name']) ?></h3>
                    <span class="status-badge <?= $request['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                    </span>
                </div>

                <div class="request-meta">
                    <span>Solicitud #<?= $request['id'] ?></span>
                    <span><?= date('d/m/Y', strtotime($request['created_at'])) ?></span>
                </div>

                <?php if (!empty($request['description'])): ?>
                <p class="request-description">
                    <?= nl2br(htmlspecialchars($request['description'])) ?>
                </p>
                <?php endif; ?>

                <a href="request_detail.php?id=<?= $request['id'] ?>" class="btn">
                    Ver detalles
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
