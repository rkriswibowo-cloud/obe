<?php
// app/Models/AuditLogModel.php

class AuditLogModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($limit = 100) {
        $stmt = $this->db->prepare("
            SELECT a.*, u.nama_lengkap 
            FROM audit_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            ORDER BY a.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function log($modul, $aksi, $deskripsi, $userId = null, $username = null) {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        if ($username === null && isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (user_id, username, modul, aksi, deskripsi, ip_address)
            VALUES (:user_id, :username, :modul, :aksi, :deskripsi, :ip_address)
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'username' => $username,
            'modul' => $modul,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'ip_address' => $ip
        ]);
    }
}
