<?php
// app/Controllers/PemetaanMkCplController.php

class PemetaanMkCplController {
    private $kurikulumModel;
    private $mkModel;
    private $cplModel;
    private $mkCplModel;
    private $access;

    public function __construct() {
        require_once __DIR__ . '/../Models/KurikulumModel.php';
        require_once __DIR__ . '/../Models/MataKuliahModel.php';
        require_once __DIR__ . '/../Models/CplModel.php';
        require_once __DIR__ . '/../Models/MkCplModel.php';
        
        $this->kurikulumModel = new KurikulumModel();
        $this->mkModel = new MataKuliahModel();
        $this->cplModel = new CplModel();
        $this->mkCplModel = new MkCplModel();
        $this->access = new AccessContext();
    }

    public function index() {
        $kurikulum_id = $_GET['kurikulum_id'] ?? null;
        $kurikulumList = $this->access->filterKurikulumRows($this->kurikulumModel->getAll());
        if ($kurikulum_id && !$this->access->isSuperAdmin()) $this->access->assertKurikulum($kurikulum_id);
        
        // Jika tidak ada kurikulum_id yang dipilih, pilih yang pertama (jika ada)
        if (!$kurikulum_id && count($kurikulumList) > 0) {
            $kurikulum_id = $kurikulumList[0]['id'];
        }

        $cplList = [];
        $mkList = [];
        $mapping = [];

        if ($kurikulum_id) {
            // Filter CPL and MK based on the selected kurikulum
            $allCpl = $this->cplModel->getAll();
            foreach($allCpl as $cpl) {
                if ($cpl['kurikulum_id'] == $kurikulum_id) {
                    $cplList[] = $cpl;
                }
            }

            $allMk = $this->mkModel->getAll();
            foreach($allMk as $mk) {
                if ($mk['kurikulum_id'] == $kurikulum_id) {
                    $mkList[] = $mk;
                }
            }

            $mapping = $this->mkCplModel->getMappingByKurikulum($kurikulum_id);
        }

        require_once __DIR__ . '/../../views/pemetaan/index.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kurikulum_id = $_POST['kurikulum_id'] ?? null;
            if (!$this->access->isSuperAdmin()) $this->access->assertKurikulum($kurikulum_id);
            $mappingData = $_POST['mapping'] ?? []; 
            // format: mapping[mk_id][] = cpl_id

            // Dapatkan semua MK untuk kurikulum ini
            $mkList = [];
            $allMk = $this->mkModel->getAll();
            foreach($allMk as $mk) {
                if ($mk['kurikulum_id'] == $kurikulum_id) {
                    $mkList[] = $mk['id'];
                }
            }

            // Bersihkan mapping lama untuk MK dalam kurikulum ini
            if (count($mkList) > 0) {
                $this->mkCplModel->clearMappingForMKs($mkList);
            }

            // Simpan mapping baru
            foreach ($mappingData as $mk_id => $cpl_ids) {
                if (!$this->access->isSuperAdmin()) $this->access->assertMataKuliah($mk_id);
                foreach ($cpl_ids as $cpl_id) {
                    if (!$this->access->isSuperAdmin()) $this->access->assertCpl($cpl_id);
                    $this->mkCplModel->saveMapping($mk_id, $cpl_id);
                }
            }

            $_SESSION['success'] = "Pemetaan Mata Kuliah ke CPL berhasil disimpan.";
            
            $redirectUrl = ($config['base_url'] ?? '/obe/public') . '/pemetaan-mk-cpl';
            if ($kurikulum_id) {
                $redirectUrl .= "?kurikulum_id=" . $kurikulum_id;
            }
            
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}
