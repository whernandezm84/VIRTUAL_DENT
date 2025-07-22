<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../includes/functions.php';

// Autenticación JWT
function authenticateJWT() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token no proporcionado']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    // Aquí implementarías la lógica de validación JWT real
    return json_decode(base64_decode(explode('.', $token)[1]), true);
}

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Enrutamiento básico de API
switch (true) {
    case preg_match('/\/api\/services/', $request) && $method == 'GET':
        $jwt = authenticateJWT();
        $db = db_connect();

        $services = [];
        $result = mysqli_query($db, "SELECT id, name, description, price FROM services WHERE active = 1");
        while($row = mysqli_fetch_assoc($result)) {
            $services[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $services
        ]);
        break;

    case preg_match('/\/api\/requests/', $request) && $method == 'POST':
        $jwt = authenticateJWT();
        $input = json_decode(file_get_contents('php://input'), true);

        // Validación y creación de solicitud
        if(!empty($input['service_id'])) {
            $db = db_connect();
            $query = "INSERT INTO requests (user_id, service_id) VALUES (?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, 'ii', $jwt['user_id'], $input['service_id']);

            if(mysqli_stmt_execute($stmt)) {
                http_response_code(201);
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear solicitud']);
            }
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint no encontrado']);
}
?>
