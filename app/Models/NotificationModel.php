<?php
// app/Models/NotificationModel.php

class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getLatest($userId = null, $limit = 10) {
        $sql = "
            SELECT * FROM notifikasi 
            WHERE (user_id IS NULL" . ($userId !== null ? " OR user_id = :user_id" : "") . ")
            ORDER BY created_at DESC 
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        if ($userId !== null) {
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount($userId = null) {
        $sql = "
            SELECT COUNT(*) FROM notifikasi 
            WHERE is_read = 0 
            AND (user_id IS NULL" . ($userId !== null ? " OR user_id = :user_id" : "") . ")
        ";
        $stmt = $this->db->prepare($sql);
        if ($userId !== null) {
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function markAllAsRead($userId = null) {
        $sql = "
            UPDATE notifikasi 
            SET is_read = 1 
            WHERE is_read = 0 
            AND (user_id IS NULL" . ($userId !== null ? " OR user_id = :user_id" : "") . ")
        ";
        $stmt = $this->db->prepare($sql);
        if ($userId !== null) {
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public function add($judul, $pesan, $tipe = 'info', $tautan = null, $icon = 'fa-solid fa-bell', $userId = null) {
        $stmt = $this->db->prepare("
            INSERT INTO notifikasi (user_id, judul, pesan, tipe, icon, tautan, is_read)
            VALUES (:user_id, :judul, :pesan, :tipe, :icon, :tautan, 0)
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'icon' => $icon,
            'tautan' => $tautan
        ]);
    }
}
