<?php
require_once '../includes/check_admin.php';
require_once '../includes/functions.php';

$db = db_connect();
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$filter = $_GET['status'] ?? 'all';

// Construir consulta con filtros
$query = "SELECT r.*, u.name as client_name, u.email, s.name as service_name
          FROM requests r
          JOIN users u ON r.user_id = u.id
          JOIN services s ON r.service_id = s.id
          WHERE 1=1";

$params = [];

if ($filter !== 'all') {
    $query .= " AND r.status = ?";
    $params[] = $filter;
}

if (!empty($_GET['search'])) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR s.name LIKE ?)";
    $searchTerm = '%' . $_GET['search'] . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Contar total para paginación
$countStmt = $db->prepare(str_replace("r.*, u.name as client_name, u.email, s.name as service_name", "COUNT(*) as total", $query));
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

// Consulta principal con paginación
$query .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Solicitudes</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container">
        <h1>Gestión de Solicitudes</h1>

        <!-- Filtros y búsqueda -->
        <div class="filter-bar">
            <form method="get" class="search-form">
                <input type="text" name="search" placeholder="Buscar...">
                <button type="submit">Buscar</button>
            </form>

            <div class="status-filter">
                <a href="?status=all" class="<?= $filter === 'all' ? 'active' : '' ?>">Todas</a>
                <a href="?status=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pendientes</a>
                <a href="?status=in_progress" class="<?= $filter === 'in_progress' ? 'active' : '' ?>">En Proceso</a>
                <a href="?status=completed" class="<?= $filter === 'completed' ? 'active' : '' ?>">Completadas</a>
            </div>
        </div>

        <!-- Tabla de solicitudes -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= $request['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($request['client_name']) ?><br>
                        <small><?= htmlspecialchars($request['email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($request['service_name']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                    <td>
                        <span class="status-badge <?= $request['status'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <a href="request_detail.php?id=<?= $request['id'] ?>" class="btn btn-sm">Ver</a>
                        <?php if ($request['status'] === 'pending'): ?>
                        <a href="process_request.php?id=<?= $request['id'] ?>" class="btn btn-sm btn-primary">Procesar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&status=<?= $filter ?>" class="page-link">Anterior</a>
            <?php endif; ?>

            <span class="current-page">Página <?= $page ?> de <?= ceil($total/$limit) ?></span>

            <?php if ($page * $limit < $total): ?>
            <a href="?page=<?= $page+1 ?>&status=<?= $filter ?>" class="page-link">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
