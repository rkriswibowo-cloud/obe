<?php
// app/Controllers/AuthController.php

class AuthController {
    public function loginView() {
        if (isset($_SESSION['user_id'])) {
            header("Location: /obe/public/dashboard");
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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role'] = $user['role_name'];
            
            header("Location: /obe/public/dashboard");
            exit;
        } else {
            $_SESSION['error'] = "Username atau password salah.";
            header("Location: /obe/public/login");
            exit;
        }
    }

    public function logout() {
        session_destroy();
        header("Location: /obe/public/login");
        exit;
    }
}
