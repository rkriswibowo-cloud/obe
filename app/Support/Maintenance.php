<?php

if (!class_exists('Database') && is_file(dirname(__DIR__, 2) . '/config/database.php')) {
    require_once dirname(__DIR__, 2) . '/config/database.php';
}

class Maintenance
{
    private const MAX_FILE_SIZE = 3145728; // 3 MB

    private const DEFAULTS = [
        'maintenance_mode' => '0',
        'maintenance_title' => 'Website Sedang Dalam Pemeliharaan',
        'maintenance_message' => 'Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi (SIP-OKE) saat ini sedang dalam proses pemeliharaan berkala untuk peningkatan performa, pembaruan fitur, dan keandalan sistem. Seluruh menu dan layanan sementara dinonaktifkan hingga proses pemeliharaan selesai.',
        'maintenance_until' => '',
        'maintenance_img' => 'assets/images/maintenance.png',
        'updated_at' => null,
    ];

    private static ?array $cachedSettings = null;

    public static function getSettings(): array
    {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }

        $settings = self::DEFAULTS;

        try {
            if (class_exists('Database')) {
                $db = Database::getInstance();
                $stmt = $db->query("SELECT setting_key, setting_value, updated_at FROM app_settings WHERE setting_key LIKE 'maintenance_%'");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                    if (!empty($row['updated_at'])) {
                        $settings['updated_at'] = $row['updated_at'];
                    }
                }
            }
        } catch (Throwable $e) {
            // Fallback to JSON file if database is not available
            $jsonPath = self::configPath();
            if (is_file($jsonPath)) {
                $data = json_decode((string)file_get_contents($jsonPath), true);
                if (is_array($data)) {
                    $settings = array_merge($settings, $data);
                }
            }
        }

        self::$cachedSettings = $settings;
        return $settings;
    }

    public static function isActive(): bool
    {
        $settings = self::getSettings();
        return ($settings['maintenance_mode'] ?? '0') === '1';
    }

    public static function isSuperAdmin(): bool
    {
        return ($_SESSION['role'] ?? '') === 'Super Admin';
    }

    public static function saveSettings(array $data): bool
    {
        $current = self::getSettings();
        $updated = array_merge($current, $data);
        $updated['updated_at'] = date('Y-m-d H:i:s');

        try {
            if (class_exists('Database')) {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
                ");
                foreach (['maintenance_mode', 'maintenance_title', 'maintenance_message', 'maintenance_until', 'maintenance_img'] as $key) {
                    if (array_key_exists($key, $updated)) {
                        $stmt->execute([$key, (string)$updated[$key]]);
                    }
                }
            }
        } catch (Throwable $e) {
            // Continue to save backup JSON
        }

        // Also save to config/maintenance.json as persistent cache/fallback
        @file_put_contents(
            self::configPath(),
            json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );

        self::$cachedSettings = $updated;
        return true;
    }

    public static function toggle(bool $enable): bool
    {
        return self::saveSettings([
            'maintenance_mode' => $enable ? '1' : '0'
        ]);
    }

    public static function baseUrl(?string $providedBase = null): string
    {
        if ($providedBase !== null) {
            return rtrim($providedBase, '/');
        }

        $sn = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (strpos($sn, '/obe/public') !== false) {
            return '/obe/public';
        } elseif (strpos($sn, '/obe') !== false) {
            return '/obe';
        }

        return '';
    }

    public static function assetUrl(?string $path, ?string $baseUrl = null): string
    {
        $base = self::baseUrl($baseUrl);

        if (empty($path)) {
            $abs = dirname(__DIR__, 2) . '/public/assets/images/maintenance.png';
            $v = is_file($abs) ? '?v=' . filemtime($abs) : '';
            return $base . '/assets/images/maintenance.png' . $v;
        }

        $cleanPath = ltrim($path, '/');
        if (strpos($cleanPath, 'obe/public/') === 0) {
            $cleanPath = substr($cleanPath, strlen('obe/public/'));
        } elseif (strpos($cleanPath, 'obe/') === 0) {
            $cleanPath = substr($cleanPath, strlen('obe/'));
        }

        $abs = dirname(__DIR__, 2) . '/public/' . $cleanPath;
        $v = is_file($abs) ? '?v=' . filemtime($abs) : '';

        return $base . '/' . $cleanPath . $v;
    }

    public static function storeUpload(string $field, array &$errors): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = 'Berkas gambar pemeliharaan gagal diunggah.';
            return null;
        }
        if (($file['size'] ?? 0) < 1 || $file['size'] > self::MAX_FILE_SIZE) {
            $errors[] = 'Ukuran gambar maksimal 3 MB.';
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];

        if (!isset($allowed[$mime])) {
            $errors[] = 'Format gambar tidak didukung. Gunakan format PNG, JPG, WebP, atau SVG.';
            return null;
        }

        $dir = self::uploadDirectory();
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            $errors[] = 'Direktori unggahan pemeliharaan tidak dapat dibuat.';
            return null;
        }

        $filename = 'maint-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors[] = 'Gagal menyimpan berkas gambar ke server.';
            return null;
        }

        return 'uploads/maintenance/' . $filename;
    }

    public static function removeUpload(?string $path): void
    {
        if (empty($path) || strpos($path, 'uploads/maintenance/') !== 0) {
            return;
        }
        $filename = basename($path);
        if (!preg_match('/^[a-z0-9._-]+$/i', $filename)) {
            return;
        }
        $file = self::uploadDirectory() . DIRECTORY_SEPARATOR . $filename;
        if (is_file($file)) {
            @unlink($file);
        }
    }

    public static function uploadDirectory(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads/maintenance';
    }

    public static function configPath(): string
    {
        return dirname(__DIR__, 2) . '/config/maintenance.json';
    }

    public static function renderAdminBanner(string $baseUrl): string
    {
        $trimmedBase = rtrim($baseUrl, '/');
        $previewUrl = htmlspecialchars($trimmedBase . '/dashboard?preview_maintenance=1');
        $maintUrl = htmlspecialchars($trimmedBase . '/maintenance');

        return '
        <div class="superadmin-maint-alert">
            <div class="superadmin-maint-alert-left">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><strong>Mode Maintenance Sedang AKTIF:</strong> Pengguna umum saat ini melihat tampilan pemeliharaan. Anda sedang mengakses sistem dalam hak akses Super Admin.</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <a href="' . $previewUrl . '" class="btn btn-sm btn-secondary"><i class="fa-solid fa-eye"></i> Pratinjau Tampilan Pengguna</a>
                <a href="' . $maintUrl . '" class="btn btn-sm btn-primary"><i class="fa-solid fa-screwdriver-wrench"></i> Kelola Maintenance</a>
            </div>
        </div>';
    }

    public static function renderWorkspace(array $settings, bool $isPreview, string $baseUrl): string
    {
        $trimmedBase = rtrim($baseUrl, '/');
        $title = !empty($settings['maintenance_title'])
            ? $settings['maintenance_title']
            : 'Website Sedang Dalam Pemeliharaan';
        $message = !empty($settings['maintenance_message'])
            ? $settings['maintenance_message']
            : 'Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi (SIP-OKE) saat ini sedang dalam proses pemeliharaan berkala untuk peningkatan performa, pembaruan fitur, dan keandalan sistem. Seluruh menu dan layanan sementara dinonaktifkan hingga proses pemeliharaan selesai.';
        $until = $settings['maintenance_until'] ?? '';
        $imgUrl = self::assetUrl($settings['maintenance_img'] ?? null, $baseUrl);
        $maintUrl = htmlspecialchars($trimmedBase . '/maintenance');
        $logoutUrl = htmlspecialchars($trimmedBase . '/logout');

        ob_start();
        ?>
        <div class="maint-workspace-wrap">
            <?php if ($isPreview): ?>
            <div class="maint-preview-notice">
                <div style="display:flex;align-items:center;gap:10px;">
                    <i class="fa-solid fa-eye"></i>
                    <span><strong>Mode Pratinjau (Preview Super Admin):</strong> Ini adalah tampilan yang dilihat pengguna umum saat mode maintenance aktif. Sidebar dan navigasi tetap utuh.</span>
                </div>
                <a href="<?= $maintUrl ?>" class="btn btn-sm btn-primary">Kembali ke Pengaturan</a>
            </div>
            <?php endif; ?>

            <div class="maint-card maint-card-full">
                <div class="maint-content-col">
                    <div class="maint-badge-pill">
                        <span class="maint-pulse-dot"></span>
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>MODE PEMELIHARAAN AKTIF</span>
                    </div>

                    <h1 class="maint-title"><?= htmlspecialchars($title) ?></h1>
                    <p class="maint-desc"><?= nl2br(htmlspecialchars($message)) ?></p>

                    <?php if (!empty($until)): ?>
                    <div class="maint-info-box">
                        <i class="fa-regular fa-clock"></i>
                        <div>
                            <span class="maint-info-label">Jadwal / Estimasi Selesai</span>
                            <strong class="maint-info-val"><?= htmlspecialchars($until) ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="maint-actions">
                        <button type="button" onclick="window.location.reload();" class="btn btn-primary maint-btn-reload">
                            <i class="fa-solid fa-rotate"></i> Muat Ulang Halaman
                        </button>
                        <a href="<?= $logoutUrl ?>" class="btn btn-secondary maint-btn-logout">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar (Logout)
                        </a>
                    </div>

                    <div class="maint-footer-note">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Universitas Anwar Medika &bull; SIP-OKE (Outcome Based Education)</span>
                    </div>
                </div>

                <div class="maint-visual-col">
                    <div class="maint-illustration-wrap">
                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Sedang Maintenance" class="maint-illustration-img">
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

