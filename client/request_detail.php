<?php
require_once '../includes/check_auth.php';
require_once '../includes/functions.php';

$db = db_connect();
$request_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Obtener información de la solicitud
$stmt = $db->prepare("SELECT r.*, s.name as service_name, s.description as service_description
                     FROM requests r
                     JOIN services s ON r.service_id = s.id
                     WHERE r.id = ? AND r.user_id = ?");
$stmt->execute([$request_id, $user_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    header("Location: requests.php");
    exit;
}

// Obtener mensajes
$messages = [];
$stmt = $db->prepare("SELECT m.*, u.name as sender_name, u.role as sender_role
                     FROM messages m
                     JOIN users u ON m.user_id = u.id
                     WHERE m.request_id = ?
                     ORDER BY m.created_at");
$stmt->execute([$request_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar nuevo mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $stmt = $db->prepare("INSERT INTO messages
                            (request_id, user_id, message)
                            VALUES (?, ?, ?)");
        $stmt->execute([$request_id, $user_id, $message]);

        // Marcar como no leído para el técnico/admin
        $db->prepare("UPDATE requests SET unread_admin = 1 WHERE id = ?")
           ->execute([$request_id]);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Solicitud #<?= $request['id'] ?></title>
    <link rel="stylesheet" href="../assets/css/client.css">
</head>
<body>
    <?php include '../includes/client_header.php'; ?>

    <div class="container">
        <div class="request-header">
            <h1>Solicitud #<?= $request['id'] ?></h1>
            <div class="status-badge large <?= $request['status'] ?>">
                <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
            </div>
        </div>

        <div class="request-details">
            <div class="detail-card">
                <h3>Servicio</h3>
                <p><?= htmlspecialchars($request['service_name']) ?></p>
            </div>

            <div class="detail-card">
                <h3>Fecha de creación</h3>
                <p><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></p>
            </div>

            <?php if ($request['status'] !== 'pending'): ?>
            <div class="detail-card">
                <h3>Técnico asignado</h3>
                <p><?= $request['assigned_tech'] ?? 'Por asignar' ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($request['description'])): ?>
            <div class="detail-card full-width">
                <h3>Descripción</h3>
                <p><?= nl2br(htmlspecialchars($request['description'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($request['technician_notes'])): ?>
            <div class="detail-card full-width">
                <h3>Notas del técnico</h3>
                <p><?= nl2br(htmlspecialchars($request['technician_notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Área de mensajes -->
        <div class="messages-container">
            <h2>Conversación</h2>

            <div class="messages-list">
                <?php foreach ($messages as $message): ?>
                <div class="message <?= $message['user_id'] == $user_id ? 'sent' : 'received' ?>">
                    <div class="message-header">
                        <strong><?= htmlspecialchars($message['sender_name']) ?></strong>
                        <small>(<?= $message['sender_role'] ?>)</small>
                        <span class="message-time">
                            <?= date('d/m/Y H:i', strtotime($message['created_at'])) ?>
                        </span>
                    </div>
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($message['message'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" class="message-form">
                <textarea name="message" placeholder="Escribe tu mensaje..." required></textarea>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>
</body>
</html>
