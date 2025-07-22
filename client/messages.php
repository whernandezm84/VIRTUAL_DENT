<?php
require_once '../includes/check_auth.php';
require_once '../includes/functions.php';

$db = db_connect();
$user_id = $_SESSION['user_id'];

// Enviar nuevo mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $message = mysqli_real_escape_string($db, $_POST['message']);

    $query = "INSERT INTO messages (request_id, user_id, message) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'iis', $request_id, $user_id, $message);

    mysqli_stmt_execute($stmt);
}

// Obtener conversaciones
$conversations = [];
$query = "SELECT r.id, s.name, MAX(m.created_at) as last_message
          FROM requests r
          JOIN services s ON r.service_id = s.id
          LEFT JOIN messages m ON r.id = m.request_id
          WHERE r.user_id = ?
          GROUP BY r.id";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_assoc($result)) {
    $conversations[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mis Mensajes</title>
</head>
<body>
    <div class="container">
        <h1>Mis Conversaciones</h1>

        <div class="conversation-list">
            <?php foreach($conversations as $conv): ?>
            <div class="conversation-item">
                <h3><?= htmlspecialchars($conv['name']) ?></h3>
                <p>Último mensaje: <?= date('d/m/Y H:i', strtotime($conv['last_message'])) ?></p>
                <a href="conversation.php?id=<?= $conv['id'] ?>">Ver conversación</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
