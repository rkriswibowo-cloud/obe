<?php

require_once __DIR__ . '/../Models/MataKuliahModel.php';
require_once __DIR__ . '/../Models/CplModel.php';
require_once __DIR__ . '/../Models/CpmkModel.php';
require_once __DIR__ . '/../Models/SubCpmkModel.php';

class ImportController {
    private $mkModel;
    private $cplModel;
    private $cpmkModel;
    private $subCpmkModel;
    private $db;

    public function __construct() {
        $this->mkModel = new MataKuliahModel();
        $this->cplModel = new CplModel();
        $this->cpmkModel = new CpmkModel();
        $this->subCpmkModel = new SubCpmkModel();
        $this->db = Database::getInstance();
    }

    public function index() {
        $mataKuliah = $this->mkModel->getAll();
        require __DIR__ . '/../../views/import/index.php';
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=Invalid+request');
            exit;
        }

        $type = $_POST['type'] ?? '';
        $mkId = $_POST['mk_id'] ?? null;
        
        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=File+upload+error');
            exit;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=Cannot+read+file');
            exit;
        }

        // Read header
        $header = fgetcsv($handle, 1000, ',');
        // normalize header
        $header = array_map('strtolower', array_map('trim', $header));

        $successCount = 0;
        $errorCount = 0;

        $this->db->beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) !== count($header)) continue;
                $row = array_combine($header, $data);

                if ($type === 'cpl') {
                    $kode = trim($row['kode'] ?? '');
                    $rumusan = trim($row['rumusan'] ?? '');
                    
                    if ($kode && $rumusan) {
                        $stmt = $this->db->prepare("INSERT INTO cpl (kode, rumusan) VALUES (?, ?) ON DUPLICATE KEY UPDATE rumusan = VALUES(rumusan)");
                        $stmt->execute([$kode, $rumusan]);
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } 
                else if ($type === 'cpmk') {
                    if (!$mkId) throw new Exception("Mata Kuliah harus dipilih untuk CPMK");
                    
                    $kodeCpmk = trim($row['kode_cpmk'] ?? '');
                    $rumusan = trim($row['rumusan_cpmk'] ?? '');
                    $cplKodes = trim($row['kode_cpl'] ?? '');

                    if ($kodeCpmk && $rumusan) {
                        // Check if cpmk exists
                        $stmt = $this->db->prepare("SELECT id FROM cpmk WHERE mk_id = ? AND kode = ?");
                        $stmt->execute([$mkId, $kodeCpmk]);
                        $cpmk = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($cpmk) {
                            $cpmkId = $cpmk['id'];
                            $stmt = $this->db->prepare("UPDATE cpmk SET rumusan = ? WHERE id = ?");
                            $stmt->execute([$rumusan, $cpmkId]);
                        } else {
                            $stmt = $this->db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (?, ?, ?)");
                            $stmt->execute([$mkId, $kodeCpmk, $rumusan]);
                            $cpmkId = $this->db->lastInsertId();
                        }
                        
                        // Parse CPLs
                        if ($cplKodes) {
                            $kodes = array_map('trim', explode(',', $cplKodes));
                            foreach ($kodes as $kc) {
                                // Find CPL ID
                                $stmt = $this->db->prepare("SELECT id FROM cpl WHERE kode = ?");
                                $stmt->execute([$kc]);
                                $cpl = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($cpl) {
                                    $stmt = $this->db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (?, ?)");
                                    $stmt->execute([$cpmkId, $cpl['id']]);
                                }
                            }
                        }
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
                else if ($type === 'sub_cpmk') {
                    if (!$mkId) throw new Exception("Mata Kuliah harus dipilih untuk Sub-CPMK");
                    
                    $kodeCpmk = trim($row['kode_cpmk'] ?? '');
                    $kodeSub = trim($row['kode_sub_cpmk'] ?? '');
                    $rumusan = trim($row['rumusan_sub_cpmk'] ?? '');

                    if ($kodeCpmk && $kodeSub && $rumusan) {
                        // Find CPMK ID
                        $stmt = $this->db->prepare("SELECT id FROM cpmk WHERE mk_id = ? AND kode = ?");
                        $stmt->execute([$mkId, $kodeCpmk]);
                        $cpmk = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($cpmk) {
                            // Check if Sub-CPMK exists
                            $stmt = $this->db->prepare("SELECT id FROM sub_cpmk WHERE cpmk_id = ? AND kode = ?");
                            $stmt->execute([$cpmk['id'], $kodeSub]);
                            $subCpmk = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($subCpmk) {
                                $stmt = $this->db->prepare("UPDATE sub_cpmk SET rumusan = ? WHERE id = ?");
                                $stmt->execute([$rumusan, $subCpmk['id']]);
                            } else {
                                $stmt = $this->db->prepare("INSERT INTO sub_cpmk (cpmk_id, kode, rumusan) VALUES (?, ?, ?)");
                                $stmt->execute([$cpmk['id'], $kodeSub, $rumusan]);
                            }
                            $successCount++;
                        } else {
                            $errorCount++; // CPMK not found
                        }
                    } else {
                        $errorCount++;
                    }
                }
            }

            $this->db->commit();
            fclose($handle);
            
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?success=1&count=' . $successCount . '&errors=' . $errorCount);
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            fclose($handle);
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function template() {
        $type = $_GET['type'] ?? '';
        
        $fileName = "template_{$type}.csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $fileName);
        
        $output = fopen('php://output', 'w');
        
        if ($type === 'cpl') {
            fputcsv($output, ['kode', 'rumusan']);
            fputcsv($output, ['CPL01', 'Mampu menerapkan matematika dan komputasi...']);
        } else if ($type === 'cpmk') {
            fputcsv($output, ['kode_cpl', 'kode_cpmk', 'rumusan_cpmk']);
            fputcsv($output, ['CPL01, CPL02', 'CPMK01', 'Mampu menjelaskan konsep dasar...']);
        } else if ($type === 'sub_cpmk') {
            fputcsv($output, ['kode_cpmk', 'kode_sub_cpmk', 'rumusan_sub_cpmk']);
            fputcsv($output, ['CPMK01', 'Sub-CPMK01', 'Mampu menjabarkan definisi...']);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Handle CSV text from textarea: write to temp file, then simulate file upload flow
     */
    public function uploadText() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['csv_text'])) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=Data+teks+kosong');
            exit;
        }

        $csvText = trim($_POST['csv_text']);
        $type = $_POST['type'] ?? '';
        $mkId = $_POST['mk_id'] ?? null;

        if (!$type) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=Jenis+data+harus+dipilih');
            exit;
        }

        // Write CSV text to a temp file
        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tmpFile, $csvText);

        $handle = fopen($tmpFile, 'r');
        if (!$handle) {
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=Gagal+memproses+data+teks');
            exit;
        }

        $header = fgetcsv($handle, 1000, ',');
        $header = array_map('strtolower', array_map('trim', $header));

        $successCount = 0;
        $errorCount = 0;

        $this->db->beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) !== count($header)) continue;
                $row = array_combine($header, $data);

                if ($type === 'cpl') {
                    $kode = trim($row['kode'] ?? '');
                    $rumusan = trim($row['rumusan'] ?? '');
                    
                    if ($kode && $rumusan) {
                        $stmt = $this->db->prepare("INSERT INTO cpl (kode, rumusan) VALUES (?, ?) ON DUPLICATE KEY UPDATE rumusan = VALUES(rumusan)");
                        $stmt->execute([$kode, $rumusan]);
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } 
                else if ($type === 'cpmk') {
                    if (!$mkId) throw new \Exception("Mata Kuliah harus dipilih untuk CPMK");
                    
                    $kodeCpmk = trim($row['kode_cpmk'] ?? '');
                    $rumusan = trim($row['rumusan_cpmk'] ?? '');
                    $cplKodes = trim($row['kode_cpl'] ?? '');

                    if ($kodeCpmk && $rumusan) {
                        $stmt = $this->db->prepare("SELECT id FROM cpmk WHERE mk_id = ? AND kode = ?");
                        $stmt->execute([$mkId, $kodeCpmk]);
                        $cpmk = $stmt->fetch(\PDO::FETCH_ASSOC);

                        if ($cpmk) {
                            $cpmkId = $cpmk['id'];
                            $stmt = $this->db->prepare("UPDATE cpmk SET rumusan = ? WHERE id = ?");
                            $stmt->execute([$rumusan, $cpmkId]);
                        } else {
                            $stmt = $this->db->prepare("INSERT INTO cpmk (mk_id, kode, rumusan) VALUES (?, ?, ?)");
                            $stmt->execute([$mkId, $kodeCpmk, $rumusan]);
                            $cpmkId = $this->db->lastInsertId();
                        }
                        
                        if ($cplKodes) {
                            $kodes = array_map('trim', explode(',', $cplKodes));
                            foreach ($kodes as $kc) {
                                $stmt = $this->db->prepare("SELECT id FROM cpl WHERE kode = ?");
                                $stmt->execute([$kc]);
                                $cpl = $stmt->fetch(\PDO::FETCH_ASSOC);
                                if ($cpl) {
                                    $stmt = $this->db->prepare("INSERT IGNORE INTO cpmk_cpl (cpmk_id, cpl_id) VALUES (?, ?)");
                                    $stmt->execute([$cpmkId, $cpl['id']]);
                                }
                            }
                        }
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
                else if ($type === 'sub_cpmk') {
                    if (!$mkId) throw new \Exception("Mata Kuliah harus dipilih untuk Sub-CPMK");
                    
                    $kodeCpmk = trim($row['kode_cpmk'] ?? '');
                    $kodeSub = trim($row['kode_sub_cpmk'] ?? '');
                    $rumusan = trim($row['rumusan_sub_cpmk'] ?? '');

                    if ($kodeCpmk && $kodeSub && $rumusan) {
                        $stmt = $this->db->prepare("SELECT id FROM cpmk WHERE mk_id = ? AND kode = ?");
                        $stmt->execute([$mkId, $kodeCpmk]);
                        $cpmk = $stmt->fetch(\PDO::FETCH_ASSOC);

                        if ($cpmk) {
                            $stmt = $this->db->prepare("SELECT id FROM sub_cpmk WHERE cpmk_id = ? AND kode = ?");
                            $stmt->execute([$cpmk['id'], $kodeSub]);
                            $subCpmk = $stmt->fetch(\PDO::FETCH_ASSOC);

                            if ($subCpmk) {
                                $stmt = $this->db->prepare("UPDATE sub_cpmk SET rumusan = ? WHERE id = ?");
                                $stmt->execute([$rumusan, $subCpmk['id']]);
                            } else {
                                $stmt = $this->db->prepare("INSERT INTO sub_cpmk (cpmk_id, kode, rumusan) VALUES (?, ?, ?)");
                                $stmt->execute([$cpmk['id'], $kodeSub, $rumusan]);
                            }
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    } else {
                        $errorCount++;
                    }
                }
            }

            $this->db->commit();
            fclose($handle);
            unlink($tmpFile);
            
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?success=1&count=' . $successCount . '&errors=' . $errorCount);
            exit;
        } catch (\Exception $e) {
            $this->db->rollBack();
            fclose($handle);
            unlink($tmpFile);
            header('Location: ' . ($GLOBALS['config']['base_url'] ?? '/obe/public') . '/import?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
