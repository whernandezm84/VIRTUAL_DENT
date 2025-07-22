<?php
require_once '../includes/check_admin.php';
require_once '../includes/functions.php';

$db = db_connect();
$success = '';
$error = '';

// Configuración actual
$config = [];
$stmt = $db->query("SELECT * FROM system_settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $config[$row['key']] = $row['value'];
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        foreach ($_POST['settings'] as $key => $value) {
            // Validar según el tipo de configuración
            switch ($key) {
                case 'maintenance_mode':
                    $value = isset($value) ? 1 : 0;
                    break;
                case 'email_notifications':
                    $value = isset($value) ? 1 : 0;
                    break;
                case 'default_service_days':
                    $value = max(1, min(30, intval($value)));
                    break;
                default:
                    $value = htmlspecialchars(trim($value));
            }

            $stmt = $db->prepare("INSERT INTO system_settings (key, value)
                                VALUES (?, ?)
                                ON DUPLICATE KEY UPDATE value = ?");
            $stmt->execute([$key, $value, $value]);
        }

        $db->commit();
        $success = "Configuración actualizada correctamente";

        // Actualizar valores en memoria
        foreach ($_POST['settings'] as $key => $value) {
            $config[$key] = $value;
        }

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error al actualizar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Configuración del Sistema</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container">
        <h1>Configuración del Sistema</h1>

        <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="settings-section">
                <h2>Configuración General</h2>

                <div class="form-group">
                    <label>Nombre de la Clínica</label>
                    <input type="text" name="settings[clinic_name]"
                           value="<?= htmlspecialchars($config['clinic_name'] ?? 'DentalTech') ?>" required>
                </div>

                <div class="form-group">
                    <label>Correo de Contacto</label>
                    <input type="email" name="settings[contact_email]"
                           value="<?= htmlspecialchars($config['contact_email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Tiempo Estimado Predeterminado (días)</label>
                    <input type="number" name="settings[default_service_days]"
                           value="<?= $config['default_service_days'] ?? 7 ?>" min="1" max="30">
                </div>
            </div>

            <div class="settings-section">
                <h2>Opciones del Sistema</h2>

                <div class="form-group checkbox">
                    <input type="checkbox" id="maintenance_mode" name="settings[maintenance_mode]"
                           <?= isset($config['maintenance_mode']) && $config['maintenance_mode'] ? 'checked' : '' ?>>
                    <label for="maintenance_mode">Modo Mantenimiento</label>
                </div>

                <div class="form-group checkbox">
                    <input type="checkbox" id="email_notifications" name="settings[email_notifications]"
                           <?= isset($config['email_notifications']) && $config['email_notifications'] ? 'checked' : '' ?>>
                    <label for="email_notifications">Notificaciones por Correo</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>
