<?php
require_once '../includes/check_admin.php';
require_once '../includes/functions.php';

$db = db_connect();
$action = $_GET['action'] ?? 'list';
$service_id = $_GET['id'] ?? 0;

// Operaciones CRUD
switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $duration = intval($_POST['duration']);

            $stmt = $db->prepare("INSERT INTO services (name, description, price, duration_days) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $description, $price, $duration])) {
                $_SESSION['success'] = "Servicio agregado correctamente";
                header("Location: services.php");
                exit;
            }
        }
        include 'views/admin/services/add.php';
        break;

    case 'edit':
        // Similar al caso 'add' pero con UPDATE
        break;

    case 'delete':
        $stmt = $db->prepare("UPDATE services SET active = 0 WHERE id = ?");
        if ($stmt->execute([$service_id])) {
            $_SESSION['success'] = "Servicio desactivado";
        }
        header("Location: services.php");
        exit;
        break;

    default:
        $services = [];
        $stmt = $db->query("SELECT * FROM services WHERE active = 1");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = $row;
        }
        include 'views/admin/services/list.php';
}
?>
