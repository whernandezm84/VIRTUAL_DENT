<?php
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

$db = db_connect();
$step = $_GET['step'] ?? 'request';
$token = $_GET['token'] ?? '';
$error = '';
$success = '';

switch ($step) {
    case 'request':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);

            $auth = new Auth();
            if ($auth->sendResetLink($email)) {
                $success = "Se ha enviado un enlace de restablecimiento a tu correo";
            } else {
                $error = "No se encontró una cuenta con ese correo";
            }
        }
        require 'views/auth/reset_request.php';
        break;

    case 'reset':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if (strlen($password) < 8) {
                $error = "La contraseña debe tener al menos 8 caracteres";
            } elseif ($password !== $confirm_password) {
                $error = "Las contraseñas no coinciden";
            } else {
                $auth = new Auth();
                if ($auth->processReset($token, $password)) {
                    $success = "¡Contraseña actualizada correctamente!";
                    header("Refresh: 3; url=login.php");
                } else {
                    $error = "El enlace ha expirado o es inválido";
                }
            }
        }
        require 'views/auth/reset_form.php';
        break;

    default:
        header("Location: login.php");
        exit;
}
?>
