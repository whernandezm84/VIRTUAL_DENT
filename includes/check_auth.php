<?php
session_start();

// Redirección si no está autenticado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];

    // Registrar intento no autorizado
    if (function_exists('audit_log')) {
        audit_log('ANONYMOUS', 'UNAUTHORIZED_ACCESS', 'Intento de acceso a ' . $_SERVER['REQUEST_URI']);
    }

    header('Location: /auth/login.php');
    exit();
}

// Verificar integridad de la sesión
$expected_checksum = hash('sha256', $_SESSION['user_id'].$_SESSION['user_email'].$_SERVER['HTTP_USER_AGENT']);
if ($_SESSION['session_checksum'] !== $expected_checksum) {
    session_destroy();

    audit_log($_SESSION['user_id'], 'SESSION_HIJACK', 'Intento de violación de sesión detectado');

    header('Location: /auth/login.php?error=session_invalid');
    exit();
}

// Verificar tiempo de inactividad (30 minutos)
$inactive_time = 1800; // Segundos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time)) {
    audit_log($_SESSION['user_id'], 'SESSION_EXPIRED', 'Sesión expirada por inactividad');

    session_unset();
    session_destroy();

    header('Location: /auth/login.php?error=session_expired');
    exit();
}

// Actualizar tiempo de actividad
$_SESSION['last_activity'] = time();

// Regenerar ID de sesión periódicamente para prevenir fixation
if (!isset($_SESSION['generated']) || $_SESSION['generated'] < (time() - 300)) {
    session_regenerate_id(true);
    $_SESSION['generated'] = time();
}
?>
