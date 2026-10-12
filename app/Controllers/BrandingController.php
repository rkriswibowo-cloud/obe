<?php

require_once __DIR__ . '/../Support/Branding.php';

class BrandingController
{
    private const MAX_FILE_SIZE = 2097152;

    public function index(): void
    {
        $this->authorize();
        $branding = Branding::all();
        $title = 'Logo & Favicon';
        require __DIR__ . '/../../views/branding/index.php';
    }

    public function save(): void
    {
        $this->authorize();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect();
        }

        $branding = Branding::all();
        $errors = [];
        $savedFiles = [];

        $logo = $this->storeUpload('logo', 'logo', [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
        ], $errors);
        if ($logo) {
            $savedFiles['logo'] = $logo;
        }

        $favicon = $this->storeUpload('favicon', 'favicon', [
            'image/png' => 'png',
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
        ], $errors);
        if ($favicon) {
            $savedFiles['favicon'] = $favicon;
        }

        if ($errors) {
            foreach ($savedFiles as $filename) {
                @unlink(Branding::uploadDirectory() . DIRECTORY_SEPARATOR . $filename);
            }
            $_SESSION['flash_error'] = implode(' ', $errors);
            $this->redirect();
        }

        if (!$savedFiles) {
            $_SESSION['flash_error'] = 'Pilih setidaknya satu berkas untuk diunggah.';
            $this->redirect();
        }

        $previousFiles = [];
        foreach ($savedFiles as $type => $filename) {
            $previousFiles[$type] = $branding[$type] ?? null;
            $branding[$type] = $filename;
        }

        if (!Branding::save($branding)) {
            foreach ($savedFiles as $filename) {
                $this->removeFile($filename);
            }
            $_SESSION['flash_error'] = 'Konfigurasi identitas tidak dapat disimpan. Periksa izin direktori config.';
            $this->redirect();
        }

        foreach ($previousFiles as $filename) {
            $this->removeFile($filename);
        }

        $_SESSION['flash_success'] = 'Logo dan favicon berhasil diperbarui.';
        $this->redirect();
    }

    public function reset(): void
    {
        $this->authorize();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect();
        }

        $branding = Branding::all();
        foreach (['logo', 'favicon'] as $type) {
            $this->removeFile($branding[$type] ?? null);
        }
        Branding::save(['logo' => null, 'favicon' => null]);
        $_SESSION['flash_success'] = 'Identitas visual dikembalikan ke tampilan bawaan SIP-OKE.';
        $this->redirect();
    }

    private function storeUpload(string $field, string $prefix, array $allowedMimes, array &$errors): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = ucfirst($field) . ' gagal diunggah.';
            return null;
        }
        if (($file['size'] ?? 0) < 1 || $file['size'] > self::MAX_FILE_SIZE) {
            $errors[] = ucfirst($field) . ' harus berukuran maksimal 2 MB.';
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset($allowedMimes[$mime])) {
            $errors[] = ucfirst($field) . ' memiliki format yang tidak didukung.';
            return null;
        }

        $directory = Branding::uploadDirectory();
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            $errors[] = 'Direktori penyimpanan identitas tidak dapat dibuat.';
            return null;
        }

        $filename = $prefix . '-' . bin2hex(random_bytes(6)) . '.' . $allowedMimes[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
            $errors[] = ucfirst($field) . ' tidak dapat disimpan.';
            return null;
        }
        return $filename;
    }

    private function removeFile(?string $filename): void
    {
        if (!$filename || !preg_match('/^[a-z0-9._-]+$/i', $filename)) {
            return;
        }
        $path = Branding::uploadDirectory() . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function authorize(): void
    {
        if (($_SESSION['role'] ?? '') !== 'Super Admin') {
            http_response_code(403);
            exit('403 Forbidden: Pengaturan identitas hanya dapat diakses oleh Super Admin.');
        }
    }

    private function redirect(): void
    {
        header('Location: /obe/public/branding');
        exit;
    }
}
