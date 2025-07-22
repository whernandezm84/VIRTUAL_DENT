<?php
require_once '../config/bootstrap.php';
header("Content-Type: application/json");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generate_jwt($user_id, $role) {
    $secret_key = $_ENV['JWT_SECRET'];
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60); // Válido por 1 hora

    $payload = [
        'iat' => $issued_at,
        'exp' => $expiration_time,
        'sub' => $user_id,
        'role' => $role
    ];

    return JWT::encode($payload, $secret_key, 'HS256');
}

$db = db_connect();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        try {
            if (!isset($input['email'], $input['password'])) {
                throw new Exception('Email y contraseña requeridos');
            }

            $stmt = $db->prepare("SELECT id, password, role FROM users WHERE email = ? AND active = 1");
            $stmt->execute([$input['email']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($input['password'], $user['password'])) {
                throw new Exception('Credenciales inválidas');
            }

            $token = generate_jwt($user['id'], $user['role']);

            echo json_encode([
                'status' => 'success',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'role' => $user['role']
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
?>
