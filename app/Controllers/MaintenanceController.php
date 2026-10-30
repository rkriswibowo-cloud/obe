<?php

require_once __DIR__ . '/../Support/Maintenance.php';
require_once __DIR__ . '/../Models/AuditLogModel.php';

class MaintenanceController
{
    private function authorize(): void
    {
        if (($_SESSION['role'] ?? '') !== 'Super Admin') {
            http_response_code(403);
            exit('403 Forbidden: Fitur pemeliharaan sistem hanya dapat diakses oleh Super Admin.');
        }
    }

    private function getBasePrefix(): string
    {
        $sn = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (strpos($sn, '/obe/public') !== false) {
            return '/obe/public';
        } elseif (strpos($sn, '/obe') !== false) {
            return '/obe';
        }
        return '';
    }

    public function index(): void
    {
        $this->authorize();

        $config = require __DIR__ . '/../../config/app.php';
        $settings = Maintenance::getSettings();
        $isActive = Maintenance::isActive();
        $title = 'Mode Pemeliharaan (Maintenance)';

        require __DIR__ . '/../../views/maintenance/index.php';
    }

    public function toggle(): void
    {
        $this->authorize();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect();
        }

        $action = $_POST['action'] ?? '';
        $newMode = ($action === 'enable');

        Maintenance::toggle($newMode);

        $statusText = $newMode ? 'AKTIF' : 'NONAKTIF';
        try {
            (new AuditLogModel())->log(
                'Maintenance',
                'Toggle',
                'Mengubah status Mode Pemeliharaan ke: ' . $statusText
            );
        } catch (Throwable $e) {}

        $_SESSION['flash_success'] = 'Status Mode Pemeliharaan berhasil diubah menjadi: ' . $statusText;
        $this->redirect();
    }

    public function save(): void
    {
        $this->authorize();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect();
        }

        $currentSettings = Maintenance::getSettings();
        $errors = [];

        $title = trim($_POST['maintenance_title'] ?? '');
        $message = trim($_POST['maintenance_message'] ?? '');
        $until = trim($_POST['maintenance_until'] ?? '');
        $mode = isset($_POST['maintenance_mode']) ? '1' : '0';

        if ($title === '') {
            $title = 'Website Sedang Dalam Pemeliharaan';
        }

        $payload = [
            'maintenance_mode' => $mode,
            'maintenance_title' => $title,
            'maintenance_message' => $message,
            'maintenance_until' => $until,
        ];

        // Handle Image Upload
        $uploaded = Maintenance::storeUpload('maintenance_img', $errors);
        if ($uploaded) {
            // Remove previous custom upload if any
            if (!empty($currentSettings['maintenance_img']) && strpos($currentSettings['maintenance_img'], 'uploads/maintenance/') === 0) {
                Maintenance::removeUpload($currentSettings['maintenance_img']);
            }
            $payload['maintenance_img'] = $uploaded;
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $this->redirect();
        }

        Maintenance::saveSettings($payload);

        try {
            (new AuditLogModel())->log(
                'Maintenance',
                'Update Settings',
                'Memperbarui konfigurasi teks dan jadwal Mode Pemeliharaan'
            );
        } catch (Throwable $e) {}

        $_SESSION['flash_success'] = 'Pengaturan Mode Pemeliharaan berhasil disimpan.';
        $this->redirect();
    }

    public function resetImage(): void
    {
        $this->authorize();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect();
        }

        $currentSettings = Maintenance::getSettings();
        if (!empty($currentSettings['maintenance_img']) && strpos($currentSettings['maintenance_img'], 'uploads/maintenance/') === 0) {
            Maintenance::removeUpload($currentSettings['maintenance_img']);
        }

        Maintenance::saveSettings([
            'maintenance_img' => 'assets/images/maintenance.png'
        ]);

        try {
            (new AuditLogModel())->log(
                'Maintenance',
                'Reset Image',
                'Mengembalikan gambar ilustrasi pemeliharaan ke gambar bawaan sistem'
            );
        } catch (Throwable $e) {}

        $_SESSION['flash_success'] = 'Gambar ilustrasi pemeliharaan dikembalikan ke gambar bawaan.';
        $this->redirect();
    }

    private function redirect(): void
    {
        header('Location: ' . $this->getBasePrefix() . '/maintenance');
        exit;
    }
}

