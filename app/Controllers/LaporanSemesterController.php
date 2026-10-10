<?php

require_once __DIR__ . '/../Models/LaporanSemesterModel.php';

class LaporanSemesterController
{
    private $model;
    private $access;

    public function __construct()
    {
        $this->model = new LaporanSemesterModel();
        $this->access = new AccessContext();
    }

    public function index()
    {
        $tahunAkademik = $this->model->getTahunAkademik();
        $kurikulumList = $this->model->getKurikulum();
        $kurikulumList = $this->access->filterKurikulumRows($kurikulumList);

        $selectedTaId = isset($_GET['tahun_akademik_id']) ? (int)$_GET['tahun_akademik_id'] : 0;
        if (!$selectedTaId) {
            foreach ($tahunAkademik as $ta) {
                if (!empty($ta['is_active'])) {
                    $selectedTaId = (int)$ta['id'];
                    break;
                }
            }
        }
        if (!$selectedTaId && !empty($tahunAkademik)) {
            $selectedTaId = (int)$tahunAkademik[0]['id'];
        }

        $validTaIds = array_map('intval', array_column($tahunAkademik, 'id'));
        if ($selectedTaId && !in_array($selectedTaId, $validTaIds, true)) {
            $selectedTaId = 0;
        }

        $selectedKurikulumId = isset($_GET['kurikulum_id']) && $_GET['kurikulum_id'] !== ''
            ? (int)$_GET['kurikulum_id']
            : null;
        if ($selectedKurikulumId && !$this->access->isSuperAdmin() && $this->access->isKaprodi()) $this->access->assertKurikulum($selectedKurikulumId);
        $validKurikulumIds = array_map('intval', array_column($kurikulumList, 'id'));
        if ($selectedKurikulumId && !in_array($selectedKurikulumId, $validKurikulumIds, true)) {
            $selectedKurikulumId = null;
        }

        $report = $selectedTaId
            ? $this->model->getReport($selectedTaId, $selectedKurikulumId)
            : ['rekap' => [], 'summary' => ['mahasiswa' => 0, 'kelas' => 0, 'mata_kuliah' => 0, 'cpl_terukur' => 0, 'cpl_tercapai' => 0, 'rata_rata' => 0], 'mata_kuliah' => []];

        require __DIR__ . '/../../views/laporan_semester/index.php';
    }
}
