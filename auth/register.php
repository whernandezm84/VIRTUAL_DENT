<?php
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

$db = db_connect();
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone'] ?? '');
    $accept_terms = isset($_POST['accept_terms']);

    // Validaciones
    if (empty($name) || strlen($name) < 3) {
        $error = "El nombre debe tener al menos 3 caracteres";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (!$accept_terms) {
        $error = "Debes aceptar los términos y condiciones";
    } else {
        // Verificar si el email ya existe
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Este correo electrónico ya está registrado";
        } else {
            // Registrar al usuario
            $auth = new Auth();
            $register_result = $auth->register($name, $email, $password, $phone);

            if ($register_result) {
                $success = "¡Registro exitoso! Por favor verifica tu correo electrónico";

                // Redirección después de 3 segundos
                header("Refresh: 3; url=login.php");
            } else {
                $error = "Error al registrar el usuario. Por favor intenta nuevamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - DentalTech</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <h1 class="text-center">Crear nueva cuenta</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Teléfono (opcional)</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="accept_terms" name="accept_terms" class="form-check-input" required>
                    <label for="accept_terms" class="form-check-label">
                        Acepto los <a href="/terms.php" target="_blank">términos y condiciones</a>
                        y la <a href="/privacy.php" target="_blank">política de privacidad</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php">¿Ya tienes una cuenta? Inicia sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
