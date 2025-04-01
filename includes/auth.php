<?php
require_once 'db.php';

class Auth {
    public static function login($username, $password) {
        global $db;
        
        $username = $db->sanitize($username);
        $stmt = $db->prepare("SELECT id, password FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_logged_in'] = true;
                return true;
            }
        }
        return false;
    }

    public static function check() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
?>