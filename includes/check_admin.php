<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
    header("Location: /auth/login.php");
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Acceso denegado</title>
        <link rel="stylesheet" href="/assets/css/errors.css">
    </head>
    <body>
        <div class="error-container">
            <h1>403 - Acceso restringido</h1>
            <p>No tienes permisos para acceder a esta área.</p>
            <a href="/" class="btn">Volver al inicio</a>
        </div>
    </body>
    </html>';
    exit;
}
?>
