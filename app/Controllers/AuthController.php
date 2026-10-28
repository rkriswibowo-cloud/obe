<?php
// app/Controllers/AuthController.php

class AuthController {
    private function getBasePrefix(): string {
        $sn = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (strpos($sn, '/obe/public') !== false) {
            return '/obe/public';
        } elseif (strpos($sn, '/obe') !== false) {
            return '/obe';
        }
        return '';
    }

    public function loginView() {
        if (isset($_SESSION['user_id'])) {
            header("Location: " . $this->getBasePrefix() . "/dashboard");
            exit;
        }
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = !empty($user['email']) ? $user['email'] : ($user['username'] . '@obe.ac.id');
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['prodi_id'] = isset($user['prodi_id']) ? (int)$user['prodi_id'] : null;
            $kaprodiStmt = $db->prepare("SELECT id FROM program_studi WHERE kaprodi_user_id = ? AND status = 'ACTIVE' ORDER BY id");
            $kaprodiStmt->execute([$user['id']]);
            $_SESSION['kaprodi_prodi_ids'] = array_map('intval', $kaprodiStmt->fetchAll(PDO::FETCH_COLUMN));
            $_SESSION['just_logged_in'] = true;
            
            header("Location: " . $this->getBasePrefix() . "/dashboard");
            exit;
        } else {
            $_SESSION['error'] = "Username atau password salah.";
            header("Location: " . $this->getBasePrefix() . "/login");
            exit;
        }
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header("Location: " . $this->getBasePrefix() . "/login");
        exit;
    }
}
