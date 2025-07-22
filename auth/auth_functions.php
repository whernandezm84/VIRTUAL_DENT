<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = db_connect();
    }

    public function register($name, $email, $password, $role = 'client') {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(32));

        $query = "INSERT INTO users (name, email, password, role, verification_token)
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $query);

        mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $hashed_password, $role, $token);
        $result = mysqli_stmt_execute($stmt);

        if($result) {
            // Enviar email de verificación
            $this->sendVerificationEmail($email, $token);
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $query = "SELECT id, password, role FROM users WHERE email = ? AND verified = 1 LIMIT 1";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function sendPasswordReset($email) {
        // Implementación completa de reseteo
    }

    private function sendVerificationEmail($email, $token) {
        // Implementación real de envío de email
    }
}
?>
