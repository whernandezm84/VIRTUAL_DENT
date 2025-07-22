<?php
require_once '../includes/check_admin.php';
require_once '../includes/functions.php';

$db = db_connect();
$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? 0;

try {
    switch ($action) {
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $role = $_POST['role'];
                $password = bin2hex(random_bytes(8)); // Contraseña temporal

                $stmt = $db->prepare("INSERT INTO users
                                    (name, email, password, role, email_verified_at)
                                    VALUES (?, ?, ?, ?, NOW())");
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt->execute([$name, $email, $hashed_password, $role]);

                // Enviar email con credenciales
                send_welcome_email($email, $name, $password);

                $_SESSION['success'] = "Usuario creado exitosamente. Se ha enviado un correo con las credenciales.";
                header("Location: users.php");
                exit;
            }
            include 'templates/users/form.php';
            break;

        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $role = $_POST['role'];
                $active = isset($_POST['active']) ? 1 : 0;

                $stmt = $db->prepare("UPDATE users
                                    SET name = ?, email = ?, role = ?, active = ?
                                    WHERE id = ?");
                $stmt->execute([$name, $email, $role, $active, $user_id]);

                $_SESSION['success'] = "Usuario actualizado correctamente";
                header("Location: users.php");
                exit;
            }

            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("Usuario no encontrado");
            }
            include 'templates/users/form.php';
            break;

        case 'delete':
            // Verificar que no sea auto-eliminación
            if ($user_id == $_SESSION['user_id']) {
                throw new Exception("No puedes eliminar tu propio usuario");
            }

            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            $_SESSION['success'] = "Usuario eliminado correctamente";
            header("Location: users.php");
            exit;
            break;

        default:
            $search = $_GET['search'] ?? '';
            $page = $_GET['page'] ?? 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $query = "SELECT id, name, email, role, active, created_at FROM users WHERE 1=1";
            $countQuery = "SELECT COUNT(*) FROM users WHERE 1=1";
            $params = [];
            $countParams = [];

            if (!empty($search)) {
                $query .= " AND (name LIKE ? OR email LIKE ?)";
                $countQuery .= " AND (name LIKE ? OR email LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $countParams[] = $searchTerm;
                $countParams[] = $searchTerm;
            }

            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            // Obtener usuarios
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener total para paginación
            $stmt = $db->prepare($countQuery);
            $stmt->execute($countParams);
            $total = $stmt->fetchColumn();

            include 'templates/users/list.php';
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: users.php");
    exit;
}
