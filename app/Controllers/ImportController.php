<?php

require_once __DIR__ . '/../Models/MataKuliahModel.php';
require_once __DIR__ . '/../Models/CplModel.php';
require_once __DIR__ . '/../Models/CpmkModel.php';
require_once __DIR__ . '/../Models/SubCpmkModel.php';
require_once __DIR__ . '/../Models/KurikulumModel.php';

class ImportController {
    private $mkModel;
    private $cplModel;
    private $cpmkModel;
    private $subCpmkModel;
    private $kurikulumModel;
    private $db;

    public function __construct() {
        $this->mkModel = new MataKuliahModel();
        $this->cplModel = new CplModel();
        $this->cpmkModel = new CpmkModel();
        $this->subCpmkModel = new SubCpmkModel();
        $this->kurikulumModel = new KurikulumModel();
        $this->db = Database::getInstance();
    }

    public function index() {
        $mataKuliah = $this->mkModel->getAll();
        $kurikulumList = $this->kurikulumModel->getAll();
        require __DIR__ . '/../../views/import/index.php';
    }

    public function upload() {
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
            $_SESSION['import_result'] = [
                'success_count' => 0,
                'error_count' => 1,
                'type' => '',
                'errors' => ['Permintaan tidak valid atau berkas belum dipilih.']
            ];
            header('Location: ' . $baseUrl . '/import');
            exit;
        }

        $type = trim($_POST['type'] ?? '');
        $kurikulumId = !empty($_POST['kurikulum_id']) ? (int)$_POST['kurikulum_id'] : null;
        $mkId = !empty($_POST['mk_id']) ? (int)$_POST['mk_id'] : null;
        $file = $_FILES['csv_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['import_result'] = [
                'success_count' => 0,
                'error_count' => 1,
                'type' => $type,
                'errors' => ['Gagal mengunggah berkas CSV (Kode error: ' . $file['error'] . ').']
            ];
            header('Location: ' . $baseUrl . '/import');
            exit;
        }

        $result = $this->processFile($file['tmp_name'], $type, $kurikulumId, $mkId);
        $_SESSION['import_result'] = $result;
        header('Location: ' . $baseUrl . '/import');
        exit;
    }

    public function uploadText() {
        $baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['csv_text'])) {
            $_SESSION['import_result'] = [
                'success_count' => 0,
                'error_count' => 1,
                'type' => '',
                'errors' => ['Data teks CSV masih kosong. Silakan tempel data terlebih dahulu.']
            ];
            header('Location: ' . $baseUrl . '/import');
            exit;
        }

        $csvText = trim($_POST['csv_text']);
        $type = trim($_POST['type'] ?? '');
        $kurikulumId = !empty($_POST['kurikulum_id']) ? (int)$_POST['kurikulum_id'] : null;
        $mkId = !empty($_POST['mk_id']) ? (int)$_POST['mk_id'] : null;

        if (!$type) {
            $_SESSION['import_result'] = [
                'success_count' => 0,
                'error_count' => 1,
                'type' => '',
                'errors' => ['Jenis data (CPL, CPMK, atau Sub-CPMK) harus dipilih.']
            ];
            header('Location: ' . $baseUrl . '/import');
            exit;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_obe_');
        file_put_contents($tmpFile, $csvText);

        $result = $this->processFile($tmpFile, $type, $kurikulumId, $mkId);
        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }

        $_SESSION['import_result'] = $result;
        header('Location: ' . $baseUrl . '/import');
        exit;
    }

    public function template() {
        $type = $_GET['type'] ?? 'cpl';
        $fileName = "template_{$type}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $fileName);

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if ($type === 'cpl') {
            fputcsv($output, ['kode', 'rumusan', 'kategori', 'target_capaian']);
            fputcsv($output, [
                'CPL-01',
                'Mampu menerapkan pemikiran logis, kritis, sistematis, dan inovatif dalam konteks pengembangan atau implementasi ilmu pengetahuan dan teknologi.',
                'Keterampilan Umum',
                '70.00'
            ]);
            fputcsv($output, [
                'CPL-02',
                'Mampu menunjukkan kinerja mandiri, bermutu, dan terukur dalam menyelesaikan tugas bidang keahlian.',
                'Keterampilan Umum',
                '75.00'
            ]);
        } elseif ($type === 'cpmk') {
            fputcsv($output, ['kode_cpl', 'kode_cpmk', 'rumusan_cpmk']);
            fputcsv($output, [
                'CPL-01, CPL-02',
                'CPMK-01',
                'Mampu menjelaskan konsep dasar, prinsip perancangan, dan arsitektur sistem informasi enterprise.'
            ]);
            fputcsv($output, [
                'CPL-02',
                'CPMK-02',
                'Mampu merancang dan mengimplementasikan modul aplikasi berbasis standar industri.'
            ]);
        } elseif ($type === 'sub_cpmk') {
            fputcsv($output, ['kode_cpmk', 'kode_sub_cpmk', 'rumusan_sub_cpmk', 'bobot', 'level_taksonomi', 'pengalaman_belajar', 'asesmen_penilaian']);
            fputcsv($output, [
                'CPMK-01',
                'Sub-CPMK 1.1',
                'Mampu memahami dan mendefinisikan konsep dasar siklus hidup pengembangan sistem.',
                '15.00',
                'C3, A2, P4',
                'Ceramah, Diskusi interaktif, Video tutorial',
                'Pretes dan Kuis'
            ]);
            fputcsv($output, [
                'CPMK-01',
                'Sub-CPMK 1.2',
                'Mampu menganalisis kebutuhan fungsional dan non-fungsional perangkat lunak.',
                '15.00',
                'C4, A3, P4',
                'Studi kasus kelompok dan Presentasi',
                'Tugas Terstruktur'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Intelligently detect CSV delimiter: comma, semicolon, tab, or pipe.
     */
    private function detectDelimiter(string $filePath): string {
        $handle = fopen($filePath, 'r');
        if (!$handle) return ',';

        $firstLine = fgets($handle);
        fclose($handle);
        if (!$firstLine) return ',';

        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

        $delimiters = [',', ';', "\t", '|'];
        $counts = [];
        foreach ($delimiters as $delim) {
            $counts[$delim] = substr_count($firstLine, $delim);
        }
        arsort($counts);
        $best = array_key_first($counts);
        return ($counts[$best] > 0) ? $best : ',';
    }

    /**
     * Process CSV file content for CPL, CPMK, or Sub-CPMK.
     */
    private function processFile(string $filePath, string $type, ?int $kurikulumId, ?int $mkId): array {
        $delimiter = $this->detectDelimiter($filePath);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [
                'success_count' => 0,
                'error_count' => 1,
                'type' => $type,
                'errors' => ['Tidak dapat membaca berkas CSV.']
            ];
        }

        $rawHeader = fgetcsv($handle, 2000, $delimiter);
        if (!$rawHeader || empty(array_filter($rawHeader))) {
            fclose($handle);
            return [
                'success_count' => 0,
                'error_count' => 1,
                'type' => $type,
                'errors' => ['Berkas CSV kosong atau tidak memiliki baris header.']
            ];
        }

        // Clean & normalize header names
        $header = array_map(function ($h) {
            $h = preg_replace('/^\xEF\xBB\xBF/', '', (string)$h);
            $h = strtolower(trim($h));
            $h = preg_replace('/[\s\-]+/', '_', $h);
            return $h;
        }, $rawHeader);

        // Map column aliases
        $colIndex = [];
        foreach ($header as $idx => $name) {
            $colIndex[$name] = $idx;
        }

        $getCol = function (array $data, array $aliases) use ($colIndex) {
            foreach ($aliases as $alias) {
                if (isset($colIndex[$alias]) && isset($data[$colIndex[$alias]])) {
                    $val = trim((string)$data[$colIndex[$alias]]);
                    if ($val !== '') return $val;
                }
            }
            return '';
        };

        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $line = 1; // Line 1 is header

        // Fetch MK info if required
        $mkData = null;
        if ($mkId) {
            $stmt = $this->db->prepare("SELECT id, kurikulum_id, kode, nama FROM mata_kuliah WHERE id = ?");
            $stmt->execute([$mkId]);
            $mkData = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Validate required parent selections
        if ($type === 'cpl' && !$kurikulumId) {
            fclose($handle);
            return [
                'success_count' => 0,
                'error_count' => 1,
                'type' => $type,
                'errors' => ['Kurikulum Tujuan wajib dipilih sebelum melakukan import CPL.']
            ];
        }

        if (($type === 'cpmk' || $type === 'sub_cpmk') && (!$mkId || !$mkData)) {
            fclose($handle);
            return [
                'success_count' => 0,
                'error_count' => 1,
                'type' => $type,
                'errors' => ['Mata Kuliah Tujuan wajib dipilih dan harus terdaftar di sistem.']
            ];
        }

        $this->db->beginTransaction();
        try {
            while (($data = fgetcsv($handle, 4000, $delimiter)) !== false) {
                $line++;
                // Skip empty lines
                if (empty(array_filter($data, static fn($v) => trim((string)$v) !== ''))) {
                    continue;
                }

                if ($type === 'cpl') {
                    $kode = $getCol($data, ['kode', 'kode_cpl', 'cpl', 'kode_cpl_capaian']);
                    $rumusan = $getCol($data, ['rumusan', 'rumusan_cpl', 'deskripsi', 'deskripsi_cpl', 'capaian']);
                    $kategori = $getCol($data, ['kategori', 'kategori_cpl', 'aspek', 'jenis']);
                    $targetStr = $getCol($data, ['target_capaian', 'target', 'bobot_target', 'standar']);
                    $target = (is_numeric($targetStr) && (float)$targetStr > 0) ? (float)$targetStr : 70.00;

                    if (!$kode || !$rumusan) {
                        $errorCount++;
                        $errors[] = "Baris {$line}: Kolom 'kode' dan 'rumusan' CPL wajib diisi.";
                        continue;
                    }

                    // Check if CPL already exists in this kurikulum
                    $normKode = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($kode));
                    $stmt = $this->db->prepare("
                        SELECT id FROM cpl 
                        WHERE kurikulum_id = ? 
                          AND (UPPER(kode) = ? OR UPPER(REPLACE(REPLACE(kode, '-', ''), ' ', '')) = ?)
                        LIMIT 1
                    ");
                    $stmt->execute([$kurikulumId, strtoupper($kode), $normKode]);
                    $existingCplId = $stmt->fetchColumn();

                    if ($existingCplId) {
                        $stmtUpd = $this->db->prepare("
                            UPDATE cpl 
                            SET rumusan = ?, 
                                kategori = COALESCE(NULLIF(?, ''), kategori), 
                                target_capaian = ?
                            WHERE id = ?
                        ");
                        $stmtUpd->execute([$rumusan, $kategori ?: null, $target, $existingCplId]);
                    } else {
                        $stmtIns = $this->db->prepare("
                            INSERT INTO cpl (kurikulum_id, kode, rumusan, kategori, target_capaian, status)
                            VALUES (?, ?, ?, ?, ?, 'ACTIVE')
                        ");
                        $stmtIns->execute([$kurikulumId, $kode, $rumusan, $kategori ?: null, $target]);
                    }
                    $successCount++;
                }

                elseif ($type === 'cpmk') {
                    $kodeCpmk = $getCol($data, ['kode_cpmk', 'cpmk', 'kode', 'kode_capaian']);
                    $rumusan = $getCol($data, ['rumusan_cpmk', 'rumusan', 'deskripsi_cpmk', 'deskripsi']);
                    $cplKodes = $getCol($data, ['kode_cpl', 'cpl', 'cpl_terkait', 'relasi_cpl']);

                    if (!$kodeCpmk || !$rumusan) {
                        $errorCount++;
                        $errors[] = "Baris {$line}: Kolom 'kode_cpmk' dan 'rumusan_cpmk' wajib diisi.";
                        continue;
                    }

                    // Check if CPMK exists in this MK
                    $normCpmk = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($kodeCpmk));
                    $stmt = $this->db->prepare("
                        SELECT id FROM cpmk 
                        WHERE mk_id = ? 
                          AND (UPPER(kode) = ? OR UPPER(REPLACE(REPLACE(kode, '-', ''), ' ', '')) = ?)
                        LIMIT 1
                    ");
                    $stmt->execute([$mkId, strtoupper($kodeCpmk), $normCpmk]);
                    $existingCpmkId = $stmt->fetchColumn();

                    if ($existingCpmkId) {
                        $cpmkId = (int)$existingCpmkId;
                        $stmtUpd = $this->db->prepare("UPDATE cpmk SET rumusan = ? WHERE id = ?");
                        $stmtUpd->execute([$rumusan, $cpmkId]);
                    } else {
                        $stmtIns = $this->db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (?, ?, ?)");
                        $stmtIns->execute([$mkId, $kodeCpmk, $rumusan]);
                        $cpmkId = (int)$this->db->lastInsertId();
                    }

                    // Map CPL relationships
                    if ($cplKodes) {
                        $rawCplList = preg_split('/[\s,;|+\/]+/', $cplKodes, -1, PREG_SPLIT_NO_EMPTY);
                        $mkKurikulumId = (int)($mkData['kurikulum_id'] ?? 0);
                        foreach ($rawCplList as $rawCode) {
                            $cplId = $this->findCplId($rawCode, $mkKurikulumId);
                            if ($cplId) {
                                $stmtRel = $this->db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (?, ?)");
                                $stmtRel->execute([$cpmkId, $cplId]);
                            }
                        }
                    }

                    $successCount++;
                }

                elseif ($type === 'sub_cpmk') {
                    $kodeCpmk = $getCol($data, ['kode_cpmk', 'cpmk', 'kode_induk']);
                    $kodeSub = $getCol($data, ['kode_sub_cpmk', 'sub_cpmk', 'kode_sub', 'kode']);
                    $rumusan = $getCol($data, ['rumusan_sub_cpmk', 'rumusan', 'deskripsi_sub_cpmk', 'deskripsi']);
                    $bobotStr = $getCol($data, ['bobot', 'bobot_persen', 'persen', 'bobot_nilai']);
                    $levelTaksonomi = $getCol($data, ['level_taksonomi', 'taksonomi', 'level']);
                    $pengalamanBelajar = $getCol($data, ['pengalaman_belajar', 'metode', 'bentuk_pembelajaran']);
                    $asesmenPenilaian = $getCol($data, ['asesmen_penilaian', 'asesmen', 'penilaian', 'evaluasi']);
                    $bobot = (is_numeric($bobotStr) && (float)$bobotStr > 0) ? (float)$bobotStr : 0.00;

                    if (!$kodeCpmk || !$kodeSub || !$rumusan) {
                        $errorCount++;
                        $errors[] = "Baris {$line}: Kolom 'kode_cpmk', 'kode_sub_cpmk', dan 'rumusan' Sub-CPMK wajib diisi.";
                        continue;
                    }

                    // Find CPMK ID in this MK
                    $cpmkId = $this->findCpmkId($mkId, $kodeCpmk);
                    if (!$cpmkId) {
                        $errorCount++;
                        $errors[] = "Baris {$line}: CPMK '{$kodeCpmk}' tidak ditemukan pada mata kuliah {$mkData['kode']} - {$mkData['nama']}.";
                        continue;
                    }

                    // Check if Sub-CPMK exists in this CPMK
                    $normSub = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($kodeSub));
                    $stmt = $this->db->prepare("
                        SELECT id FROM sub_cpmk 
                        WHERE cpmk_id = ? 
                          AND (UPPER(kode) = ? OR UPPER(REPLACE(REPLACE(kode, '-', ''), ' ', '')) = ?)
                        LIMIT 1
                    ");
                    $stmt->execute([$cpmkId, strtoupper($kodeSub), $normSub]);
                    $existingSubId = $stmt->fetchColumn();

                    if ($existingSubId) {
                        $stmtUpd = $this->db->prepare("
                            UPDATE sub_cpmk 
                            SET rumusan = ?,
                                bobot = CASE WHEN ? > 0 THEN ? ELSE bobot END,
                                level_taksonomi = COALESCE(NULLIF(?, ''), level_taksonomi),
                                pengalaman_belajar = COALESCE(NULLIF(?, ''), pengalaman_belajar),
                                asesmen_penilaian = COALESCE(NULLIF(?, ''), asesmen_penilaian)
                            WHERE id = ?
                        ");
                        $stmtUpd->execute([
                            $rumusan,
                            $bobot,
                            $bobot,
                            $levelTaksonomi ?: null,
                            $pengalamanBelajar ?: null,
                            $asesmenPenilaian ?: null,
                            $existingSubId
                        ]);
                    } else {
                        $stmtIns = $this->db->prepare("
                            INSERT INTO sub_cpmk (cpmk_id, kode, rumusan, bobot, level_taksonomi, pengalaman_belajar, asesmen_penilaian, urutan)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0)
                        ");
                        $stmtIns->execute([
                            $cpmkId,
                            $kodeSub,
                            $rumusan,
                            $bobot,
                            $levelTaksonomi ?: null,
                            $pengalamanBelajar ?: null,
                            $asesmenPenilaian ?: null
                        ]);
                    }

                    $successCount++;
                }
            }

            $this->db->commit();
            fclose($handle);

            return [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'type' => $type,
                'errors' => $errors
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            fclose($handle);
            return [
                'success_count' => 0,
                'error_count' => 1,
                'type' => $type,
                'errors' => ['Terjadi kesalahan database: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Locate CPL ID by code matching with kurikulum fallback.
     */
    private function findCplId(string $rawCode, int $mkKurikulumId): ?int {
        $clean = trim($rawCode);
        if ($clean === '') return null;

        $normalized = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($clean));

        // 1. Try matching in MK's kurikulum first
        if ($mkKurikulumId > 0) {
            $stmt = $this->db->prepare("
                SELECT id FROM cpl 
                WHERE kurikulum_id = ? 
                  AND (UPPER(kode) = ? OR UPPER(REPLACE(REPLACE(kode, '-', ''), ' ', '')) = ?)
                LIMIT 1
            ");
            $stmt->execute([$mkKurikulumId, strtoupper($clean), $normalized]);
            $res = $stmt->fetchColumn();
            if ($res) return (int)$res;
        }

        // 2. Fallback: match in any kurikulum
        $stmt = $this->db->prepare("
            SELECT id FROM cpl 
            WHERE UPPER(kode) = ? OR UPPER(REPLACE(REPLACE(kode, '-', ''), ' ', '')) = ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([strtoupper($clean), $normalized]);
        $res = $stmt->fetchColumn();
        return $res ? (int)$res : null;
    }

    /**
     * Locate CPMK ID by code in the specific Mata Kuliah.
     */
    private function findCpmkId(int $mkId, string $rawCode): ?int {
        $clean = trim($rawCode);
        if ($clean === '') return null;

        $normalized = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($clean));

        $stmt = $this->db->prepare("
            SELECT id FROM cpmk 
            WHERE mk_id = ? 
              AND (UPPER(kode) = ? OR UPPER(REPLACE(REPLACE(kode, '-', ''), ' ', '')) = ?)
            LIMIT 1
        ");
        $stmt->execute([$mkId, strtoupper($clean), $normalized]);
        $res = $stmt->fetchColumn();
        return $res ? (int)$res : null;
    }
}
