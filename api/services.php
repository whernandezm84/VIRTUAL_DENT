<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../includes/auth.php';

try {
    $db = db_connect();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $query = "SELECT id, name, description, price, duration_days
                     FROM services WHERE active = 1";

            if (isset($_GET['id'])) {
                $query .= " AND id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$_GET['id']]);
                $service = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($service) {
                    echo json_encode($service);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Servicio no encontrado']);
                }
            } else {
                $services = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['data' => $services]);
            }
            break;

        case 'POST':
            // Requiere autenticación y permisos de admin
            authorize(['admin']);

            $input = json_decode(file_get_contents('php://input'), true);
            validate_service_data($input);

            $stmt = $db->prepare("INSERT INTO services (name, description, price, duration_days)
                                VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $input['name'],
                $input['description'],
                $input['price'],
                $input['duration_days']
            ]);

            http_response_code(201);
            echo json_encode(['id' => $db->lastInsertId()]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function validate_service_data($data) {
    $required = ['name', 'description', 'price', 'duration_days'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    if (!is_numeric($data['price']) || $data['price'] <= 0) {
        throw new Exception("Precio inválido");
    }

    if (!is_numeric($data['duration_days']) || $data['duration_days'] <= 0) {
        throw new Exception("Duración inválida");
    }
}
?>
