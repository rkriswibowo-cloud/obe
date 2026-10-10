<?php
// app/Controllers/NotificationController.php

require_once __DIR__ . '/../Models/NotificationModel.php';

class NotificationController {
    private $notifModel;

    public function __construct() {
        $this->notifModel = new NotificationModel();
    }

    public function markAllRead() {
        header('Content-Type: application/json; charset=utf-8');
        $userId = $_SESSION['user_id'] ?? null;
        $success = $this->notifModel->markAllAsRead($userId);
        echo json_encode([
            'success' => $success,
            'message' => 'Semua notifikasi telah ditandai dibaca'
        ]);
        exit;
    }
}
