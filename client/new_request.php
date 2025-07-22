<?php
require_once '../includes/check_auth.php';
require_once '../includes/functions.php';

$db = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar y procesar formulario
        $service_id = intval($_POST['service_id']);
        $description = mysqli_real_escape_string($db, $_POST['description']);

        // Subir imágenes si existen
        $uploaded_images = [];
        if(!empty($_FILES['images'])) {
            foreach($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key]
                    ];
                    $uploaded_images[] = handle_file_upload($file);
                }
            }
        }

        // Insertar solicitud
        $images_json = json_encode($uploaded_images);
        $user_id = $_SESSION['user_id'];

        $query = "INSERT INTO requests
                 (user_id, service_id, description, images, status)
                 VALUES ($user_id, $service_id, '$description', '$images_json', 'pending')";

        if(mysqli_query($db, $query)) {
            $_SESSION['success'] = "Solicitud creada correctamente";
            header("Location: requests.php");
            exit;
        } else {
            throw new Exception("Error al crear la solicitud: " . mysqli_error($db));
        }

    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener servicios disponibles
$services = [];
$result = mysqli_query($db, "SELECT id, name FROM services WHERE active = 1");
while($row = mysqli_fetch_assoc($result)) {
    $services[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nueva Solicitud</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/client_header.php'; ?>

    <div class="container">
        <h1>Nueva Solicitud de Servicio</h1>

        <?php if(isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="request-form">
            <div class="form-group">
                <label for="service_id">Servicio:</label>
                <select name="service_id" id="service_id" required>
                    <option value="">Seleccione un servicio</option>
                    <?php foreach($services as $service): ?>
                    <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Descripción detallada:</label>
                <textarea name="description" id="description" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label>Imágenes de referencia (Máx. 5MB c/u):</label>
                <input type="file" name="images[]" multiple accept="image/*">
            </div>

            <button type="submit" class="btn">Enviar Solicitud</button>
        </form>
    </div>
</body>
</html>
