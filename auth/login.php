<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $auth = new Auth();
    if ($auth->login($email, $password)) {
        // Registro de login exitoso
        audit_log($_SESSION['user_id'], 'LOGIN', "Usuario inici� sesi�n");

        // Redirecci�n seg�n rol
        switch($_SESSION['user_role']) {
            case 'admin':
                header('Location: /admin/dashboard.php');
                break;
            case 'technician':
                header('Location: /technician/dashboard.php');
                break;
            default:
                header('Location: /client/dashboard.php');
        }
        exit();
    } else {
        $error = "Credenciales inv�lidas";
        audit_log(0, 'LOGIN_FAILED', "Intento fallido con email: $email");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Iniciar Sesi�n</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="login-container">
        <h1><img src="../assets/images/logo.png" alt="DentalTech Logo"></h1>

        <?php if(isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Correo Electr�nico</label>
                <input type="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label>Contrase�a</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Iniciar Sesi�n</button>
        </form>

        <div class="links">
            <a href="reset_password.php">�Olvidaste tu contrase�a?</a>
            <a href="register.php">Registrarse</a>
        </div>
    </div>
</body>
</html>
