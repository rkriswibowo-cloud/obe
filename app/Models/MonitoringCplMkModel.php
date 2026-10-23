<?php
// app/Models/MonitoringCplMkModel.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Support/AccessContext.php';

class MonitoringCplMkModel
{
    private PDO $db;
    private AccessContext $access;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->access = new AccessContext($this->db);
    }

    /**
     * Mengambil daftar Tahun Akademik
     */
    public function getTahunAkademik(): array
    {
        return $this->db->query("
            SELECT id, nama, is_active 
            FROM tahun_akademik 
            ORDER BY is_active DESC, id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil tahun akademik yang sedang aktif
     */
    public function getActiveTahunAkademik(): ?array
    {
        $stmt = $this->db->query("SELECT id, nama, is_active FROM tahun_akademik WHERE is_active = 1 LIMIT 1");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Mengambil daftar Kurikulum (dengan filtering prodi hak akses)
     */
    public function getKurikulumList(): array
    {
        $sql = "
            SELECT k.id, k.nama, k.kode, k.tahun_mulai, k.prodi_id, p.nama AS prodi_nama, p.jenjang
            FROM kurikulum k
            JOIN program_studi p ON p.id = k.prodi_id
            WHERE k.status = 'ACTIVE'
            ORDER BY k.tahun_mulai DESC, k.nama ASC
        ";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $this->access->filterKurikulumRows($rows);
    }

    /**
     * Mengambil daftar Program Studi
     */
    public function getProgramStudiList(): array
    {
        $sql = "SELECT id, kode, nama, jenjang FROM program_studi WHERE status = 'ACTIVE' ORDER BY nama ASC";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $this->access->filterProdiRows($rows);
    }

    /**
     * Mengambil daftar Mata Kuliah beserta ringkasan pemenuhan CPL mahasiswa
     */
    public function getCoursesMonitoring(
        ?int $taId = null,
        ?int $kurikulumId = null,
        ?int $prodiId = null,
        string $search = '',
        string $statusFilter = ''
    ): array {
        // Query Master Mata Kuliah
        $params = [];
        $where = ["mk.status = 'ACTIVE'"];

        if ($kurikulumId) {
            $where[] = "mk.kurikulum_id = :kurikulum_id";
            $params['kurikulum_id'] = $kurikulumId;
        }

        if ($prodiId) {
            $where[] = "k.prodi_id = :prodi_id";
            $params['prodi_id'] = $prodiId;
        }

        if ($search !== '') {
            $where[] = "(mk.kode LIKE :search OR mk.nama LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sqlCourses = "
            SELECT 
                mk.id,
                mk.kode,
                mk.nama,
                (COALESCE(mk.sks_teori, 0) + COALESCE(mk.sks_praktek, 0)) AS sks,
                mk.semester,
                mk.sifat,
                k.id AS kurikulum_id,
                k.nama AS kurikulum_nama,
                p.id AS prodi_id,
                p.nama AS prodi_nama,
                p.jenjang
            FROM mata_kuliah mk
            JOIN kurikulum k ON k.id = mk.kurikulum_id
            JOIN program_studi p ON p.id = k.prodi_id
            WHERE {$whereClause}
            ORDER BY mk.semester ASC, mk.kode ASC
        ";

        $stmt = $this->db->prepare($sqlCourses);
        $stmt->execute($params);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter berdasarkan hak akses
        if (!$this->access->isSuperAdmin() && !empty($_SESSION['role'])) {
            if ($this->access->isKaprodi()) {
                $courses = array_values(array_filter($courses, fn($c) => $this->access->canAccessProdi($c['prodi_id'])));
            } elseif ($this->access->userProdiId() !== null) {
                $courses = array_values(array_filter($courses, fn($c) => (int)$c['prodi_id'] === $this->access->userProdiId()));
            }
        }

        if (empty($courses)) {
            return [
                'courses' => [],
                'summary' => [
                    'total_courses' => 0,
                    'measured_courses' => 0,
                    'total_students' => 0,
                    'tercapai_students' => 0,
                    'avg_attainment' => 0,
                    'tercapai_courses' => 0,
                ]
            ];
        }

        $courseIds = array_map('intval', array_column($courses, 'id'));
        $courseIdsStr = implode(',', $courseIds);

        // 1. Ambil relasi CPL per Mata Kuliah (via cpmk_cpl)
        $sqlCpl = "
            SELECT 
                cpmk.mk_id,
                c.id AS cpl_id,
                c.kode AS cpl_kode,
                c.rumusan AS cpl_rumusan,
                COALESCE(c.target_capaian, 70.00) AS target_capaian,
                cpmk.id AS cpmk_id
            FROM cpmk
            JOIN cpmk_cpl cc ON cc.cpmk_id = cpmk.id
            JOIN cpl c ON c.id = cc.cpl_id
            WHERE cpmk.mk_id IN ({$courseIdsStr})
            ORDER BY c.kode ASC, cpmk.id ASC
        ";
        $cplRows = $this->db->query($sqlCpl)->fetchAll(PDO::FETCH_ASSOC);

        // Petakan CPL per MK
        $mkCplMap = [];
        foreach ($cplRows as $r) {
            $mkId = (int)$r['mk_id'];
            $cplId = (int)$r['cpl_id'];
            if (!isset($mkCplMap[$mkId][$cplId])) {
                $mkCplMap[$mkId][$cplId] = [
                    'id' => $cplId,
                    'kode' => $r['cpl_kode'],
                    'rumusan' => $r['cpl_rumusan'],
                    'target' => (float)$r['target_capaian'],
                    'cpmk_ids' => []
                ];
            }
            $mkCplMap[$mkId][$cplId]['cpmk_ids'][] = (int)$r['cpmk_id'];
        }

        // Juga periksa relasi langsung mk_cpl jika ada
        $sqlDirectCpl = "
            SELECT mc.mk_id, c.id AS cpl_id, c.kode AS cpl_kode, c.rumusan AS cpl_rumusan, COALESCE(c.target_capaian, 70.00) AS target_capaian
            FROM mk_cpl mc
            JOIN cpl c ON c.id = mc.cpl_id
            WHERE mc.mk_id IN ({$courseIdsStr})
        ";
        $directCpls = $this->db->query($sqlDirectCpl)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($directCpls as $dc) {
            $mkId = (int)$dc['mk_id'];
            $cplId = (int)$dc['cpl_id'];
            if (!isset($mkCplMap[$mkId][$cplId])) {
                $mkCplMap[$mkId][$cplId] = [
                    'id' => $cplId,
                    'kode' => $dc['cpl_kode'],
                    'rumusan' => $dc['cpl_rumusan'],
                    'target' => (float)$dc['target_capaian'],
                    'cpmk_ids' => []
                ];
            }
        }

        // 2. Ambil data Kelas & Mahasiswa (termasuk filter TA jika dipilih)
        $taFilterKelas = $taId ? " AND kl.tahun_akademik_id = " . (int)$taId : "";
        $sqlClasses = "
            SELECT 
                kl.id AS kelas_id,
                kl.mata_kuliah_id,
                kl.nama_kelas,
                km.mahasiswa_id
            FROM kelas kl
            LEFT JOIN kelas_mahasiswa km ON km.kelas_id = kl.id
            WHERE kl.mata_kuliah_id IN ({$courseIdsStr}) {$taFilterKelas}
        ";
        $classRows = $this->db->query($sqlClasses)->fetchAll(PDO::FETCH_ASSOC);

        $mkClassesCount = [];
        $mkEnrolledStudents = [];
        foreach ($classRows as $cr) {
            $mkId = (int)$cr['mata_kuliah_id'];
            $mkClassesCount[$mkId][(int)$cr['kelas_id']] = true;
            if (!empty($cr['mahasiswa_id'])) {
                $mkEnrolledStudents[$mkId][(int)$cr['mahasiswa_id']] = true;
            }
        }

        // 3. Ambil data Asesmen Nilai per Sub-CPMK
        $sqlAssessments = "
            SELECT 
                kl.mata_kuliah_id,
                an.mahasiswa_id,
                sc.cpmk_id,
                COALESCE(sc.bobot, 10.00) AS bobot,
                an.nilai
            FROM asesmen_nilai an
            JOIN kelas kl ON kl.id = an.kelas_id
            JOIN sub_cpmk sc ON sc.id = an.sub_cpmk_id
            WHERE kl.mata_kuliah_id IN ({$courseIdsStr}) {$taFilterKelas}
        ";
        $assessRows = $this->db->query($sqlAssessments)->fetchAll(PDO::FETCH_ASSOC);

        // Hitung skor CPMK per mahasiswa per MK
        $mkStudentCpmk = [];
        foreach ($assessRows as $ar) {
            $mkId = (int)$ar['mata_kuliah_id'];
            $mId = (int)$ar['mahasiswa_id'];
            $cpId = (int)$ar['cpmk_id'];
            $w = max(0.01, (float)$ar['bobot']);
            $v = (float)$ar['nilai'];

            if (!isset($mkStudentCpmk[$mkId][$mId][$cpId])) {
                $mkStudentCpmk[$mkId][$mId][$cpId] = ['sum' => 0.0, 'weight' => 0.0];
            }
            $mkStudentCpmk[$mkId][$mId][$cpId]['sum'] += ($v * $w);
            $mkStudentCpmk[$mkId][$mId][$cpId]['weight'] += $w;
        }

        // 4. Hitung agregasi capaian CPL per Mata Kuliah
        $totalAllStudents = 0;
        $totalTercapaiStudents = 0;
        $measuredCoursesCount = 0;
        $tercapaiCoursesCount = 0;
        $sumCourseAttainment = 0;

        foreach ($courses as &$c) {
            $mkId = (int)$c['id'];
            $cpls = $mkCplMap[$mkId] ?? [];
            $c['cpl_list'] = array_values($cpls);
            $c['cpl_codes'] = implode(', ', array_column($cpls, 'kode'));
            $c['total_cpl'] = count($cpls);

            $c['total_kelas'] = isset($mkClassesCount[$mkId]) ? count($mkClassesCount[$mkId]) : 0;
            $studentsList = isset($mkEnrolledStudents[$mkId]) ? array_keys($mkEnrolledStudents[$mkId]) : [];
            $c['total_mahasiswa'] = count($studentsList);

            $mhsTercapai = 0;
            $mhsBelumTercapai = 0;
            $mhsBelumDinilai = 0;
            $studentAttainments = [];

            foreach ($studentsList as $mId) {
                $assessedCpls = 0;
                $passedCpls = 0;
                $studentCplScores = [];

                foreach ($cpls as $cplId => $cplMeta) {
                    $cpmkSums = 0;
                    $cpmkCount = 0;

                    foreach ($cplMeta['cpmk_ids'] as $cpId) {
                        if (isset($mkStudentCpmk[$mkId][$mId][$cpId]) && $mkStudentCpmk[$mkId][$mId][$cpId]['weight'] > 0) {
                            $cpmkScore = $mkStudentCpmk[$mkId][$mId][$cpId]['sum'] / $mkStudentCpmk[$mkId][$mId][$cpId]['weight'];
                            $cpmkSums += $cpmkScore;
                            $cpmkCount++;
                        }
                    }

                    if ($cpmkCount > 0) {
                        $cplScore = $cpmkSums / $cpmkCount;
                        $assessedCpls++;
                        $studentCplScores[] = $cplScore;
                        if ($cplScore >= $cplMeta['target']) {
                            $passedCpls++;
                        }
                    }
                }

                if ($assessedCpls === 0) {
                    $mhsBelumDinilai++;
                } else {
                    $avgStudentScore = count($studentCplScores) > 0 ? (array_sum($studentCplScores) / count($studentCplScores)) : 0;
                    $studentAttainments[] = $avgStudentScore;

                    // Mahasiswa dinyatakan tercapai CPL jika semua CPL yang diukur di MK ini mencapai target
                    if ($passedCpls === count($cpls) && count($cpls) > 0) {
                        $mhsTercapai++;
                    } else {
                        $mhsBelumTercapai++;
                    }
                }
            }

            $c['mahasiswa_tercapai'] = $mhsTercapai;
            $c['mahasiswa_belum_tercapai'] = $mhsBelumTercapai;
            $c['mahasiswa_belum_dinilai'] = $mhsBelumDinilai;
            $totalMeasuredMhs = $mhsTercapai + $mhsBelumTercapai;
            $c['mahasiswa_terukur'] = $totalMeasuredMhs;

            $c['persentase_tercapai'] = $totalMeasuredMhs > 0 ? round(($mhsTercapai / $totalMeasuredMhs) * 100, 1) : 0;
            $c['avg_cpl_score'] = count($studentAttainments) > 0 ? round(array_sum($studentAttainments) / count($studentAttainments), 1) : 0;

            // Status evaluasi Mata Kuliah
            if ($totalMeasuredMhs === 0) {
                $c['status'] = 'Belum Dinilai';
                $c['status_badge'] = 'secondary';
            } elseif ($c['persentase_tercapai'] >= 80.0) {
                $c['status'] = 'Tercapai';
                $c['status_badge'] = 'success';
                $tercapaiCoursesCount++;
            } elseif ($c['persentase_tercapai'] >= 50.0) {
                $c['status'] = 'Sebagian Tercapai';
                $c['status_badge'] = 'warning';
            } else {
                $c['status'] = 'Belum Tercapai';
                $c['status_badge'] = 'danger';
            }

            if ($totalMeasuredMhs > 0) {
                $measuredCoursesCount++;
                $sumCourseAttainment += $c['persentase_tercapai'];
            }

            $totalAllStudents += $c['total_mahasiswa'];
            $totalTercapaiStudents += $mhsTercapai;
        }
        unset($c);

        // Filter berdasarkan status jika diminta
        if ($statusFilter !== '') {
            $courses = array_values(array_filter($courses, function ($c) use ($statusFilter) {
                if ($statusFilter === 'tercapai') return $c['status'] === 'Tercapai';
                if ($statusFilter === 'sebagian') return $c['status'] === 'Sebagian Tercapai';
                if ($statusFilter === 'belum_tercapai') return $c['status'] === 'Belum Tercapai';
                if ($statusFilter === 'belum_dinilai') return $c['status'] === 'Belum Dinilai';
                return true;
            }));
        }

        $summary = [
            'total_courses' => count($courses),
            'measured_courses' => $measuredCoursesCount,
            'total_students' => $totalAllStudents,
            'tercapai_students' => $totalTercapaiStudents,
            'avg_attainment' => $measuredCoursesCount > 0 ? round($sumCourseAttainment / $measuredCoursesCount, 1) : 0,
            'tercapai_courses' => $tercapaiCoursesCount,
        ];

        return [
            'courses' => $courses,
            'summary' => $summary,
        ];
    }

    /**
     * Mengambil detail Mata Kuliah, daftar CPL, dan rincian per mahasiswa
     */
    public function getCourseStudentDetail(
        int $mkId,
        ?int $taId = null,
        ?int $kelasId = null,
        string $statusFilter = '',
        string $searchMhs = ''
    ): ?array {
        // 1. Ambil detail Mata Kuliah
        $stmt = $this->db->prepare("
            SELECT 
                mk.id,
                mk.kode,
                mk.nama,
                (COALESCE(mk.sks_teori, 0) + COALESCE(mk.sks_praktek, 0)) AS sks,
                mk.sks_teori,
                mk.sks_praktek,
                mk.semester,
                mk.sifat,
                mk.deskripsi,
                k.id AS kurikulum_id,
                k.nama AS kurikulum_nama,
                p.id AS prodi_id,
                p.nama AS prodi_nama,
                p.jenjang
            FROM mata_kuliah mk
            JOIN kurikulum k ON k.id = mk.kurikulum_id
            JOIN program_studi p ON p.id = k.prodi_id
            WHERE mk.id = :mk_id
            LIMIT 1
        ");
        $stmt->execute(['mk_id' => $mkId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            return null;
        }

        // Cek hak akses jika role Kaprodi
        if (!$this->access->isSuperAdmin() && $this->access->isKaprodi() && !empty($_SESSION['role'])) {
            $this->access->assertMataKuliah($mkId);
        }

        // Ambil Dosen Pengampu / PJMK dari tabel mata_kuliah_pengampu atau RPS
        $stmtDosen = $this->db->prepare("
            SELECT u.nama_lengkap, u.email, u.nidn
            FROM mata_kuliah_pengampu mkp
            JOIN users u ON u.id = mkp.pjmk_user_id
            WHERE mkp.mata_kuliah_id = :mk_id
            ORDER BY mkp.id DESC
            LIMIT 1
        ");
        $stmtDosen->execute(['mk_id' => $mkId]);
        $pjmk = $stmtDosen->fetch(PDO::FETCH_ASSOC);

        if (!$pjmk) {
            $stmtRps = $this->db->prepare("SELECT dosen_pengampu FROM rps WHERE mata_kuliah_id = :mk_id LIMIT 1");
            $stmtRps->execute(['mk_id' => $mkId]);
            $rpsDosen = $stmtRps->fetchColumn();
            $course['dosen_pengampu'] = $rpsDosen ?: 'Belum Ditetapkan';
        } else {
            $course['dosen_pengampu'] = $pjmk['nama_lengkap'];
        }

        // 2. Ambil CPL yang dibebankan ke MK ini
        $stmtCpl = $this->db->prepare("
            SELECT 
                c.id,
                c.kode,
                c.rumusan,
                COALESCE(c.target_capaian, 70.00) AS target_capaian,
                cc.cpmk_id,
                cpmk.kode AS cpmk_kode
            FROM cpmk
            JOIN cpmk_cpl cc ON cc.cpmk_id = cpmk.id
            JOIN cpl c ON c.id = cc.cpl_id
            WHERE cpmk.mk_id = :mk_id
            ORDER BY c.kode ASC, cpmk.id ASC
        ");
        $stmtCpl->execute(['mk_id' => $mkId]);
        $cplRows = $stmtCpl->fetchAll(PDO::FETCH_ASSOC);

        $cpls = [];
        foreach ($cplRows as $r) {
            $cid = (int)$r['id'];
            if (!isset($cpls[$cid])) {
                $cpls[$cid] = [
                    'id' => $cid,
                    'kode' => $r['kode'],
                    'rumusan' => $r['rumusan'],
                    'target' => (float)$r['target_capaian'],
                    'cpmk_ids' => [],
                    'cpmk_kodes' => [],
                    'score_sum' => 0.0,
                    'assessed_count' => 0,
                    'passed_count' => 0,
                ];
            }
            $cpls[$cid]['cpmk_ids'][] = (int)$r['cpmk_id'];
            $cpls[$cid]['cpmk_kodes'][] = $r['cpmk_kode'];
        }

        // 3. Ambil daftar kelas untuk MK ini
        $taFilterKelas = $taId ? " AND kl.tahun_akademik_id = " . (int)$taId : "";
        $stmtKelas = $this->db->prepare("
            SELECT kl.id, kl.nama_kelas, kl.tahun_akademik_id, ta.nama AS ta_nama, COUNT(km.mahasiswa_id) AS total_mhs
            FROM kelas kl
            LEFT JOIN tahun_akademik ta ON ta.id = kl.tahun_akademik_id
            LEFT JOIN kelas_mahasiswa km ON km.kelas_id = kl.id
            WHERE kl.mata_kuliah_id = :mk_id {$taFilterKelas}
            GROUP BY kl.id, kl.nama_kelas, kl.tahun_akademik_id, ta.nama
            ORDER BY kl.nama_kelas ASC
        ");
        $stmtKelas->execute(['mk_id' => $mkId]);
        $classes = $stmtKelas->fetchAll(PDO::FETCH_ASSOC);

        // 4. Ambil Mahasiswa yang mengambil kelas pada MK ini
        $whereMhs = ["kl.mata_kuliah_id = :mk_id"];
        $paramsMhs = ['mk_id' => $mkId];

        if ($taId) {
            $whereMhs[] = "kl.tahun_akademik_id = :ta_id";
            $paramsMhs['ta_id'] = $taId;
        }

        if ($kelasId) {
            $whereMhs[] = "kl.id = :kelas_id";
            $paramsMhs['kelas_id'] = $kelasId;
        }

        if ($searchMhs !== '') {
            $whereMhs[] = "(u.username LIKE :search OR u.nama_lengkap LIKE :search)";
            $paramsMhs['search'] = '%' . $searchMhs . '%';
        }

        $whereMhsStr = implode(' AND ', $whereMhs);

        $sqlStudents = "
            SELECT 
                u.id AS mhs_id,
                u.username AS nim,
                u.nama_lengkap,
                kl.id AS kelas_id,
                kl.nama_kelas,
                kl.tahun_akademik_id,
                ta.nama AS ta_nama
            FROM kelas kl
            JOIN kelas_mahasiswa km ON km.kelas_id = kl.id
            JOIN users u ON u.id = km.mahasiswa_id
            LEFT JOIN tahun_akademik ta ON ta.id = kl.tahun_akademik_id
            WHERE {$whereMhsStr}
            ORDER BY kl.nama_kelas ASC, u.username ASC
        ";
        $stmtStudents = $this->db->prepare($sqlStudents);
        $stmtStudents->execute($paramsMhs);
        $studentRows = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        // 5. Ambil Asesmen Nilai untuk kelas MK ini
        $sqlAssess = "
            SELECT 
                an.mahasiswa_id,
                an.kelas_id,
                sc.cpmk_id,
                sc.bobot,
                an.nilai
            FROM asesmen_nilai an
            JOIN kelas kl ON kl.id = an.kelas_id
            JOIN sub_cpmk sc ON sc.id = an.sub_cpmk_id
            WHERE kl.mata_kuliah_id = :mk_id {$taFilterKelas}
        ";
        $stmtAssess = $this->db->prepare($sqlAssess);
        $stmtAssess->execute(['mk_id' => $mkId]);
        $assessRows = $stmtAssess->fetchAll(PDO::FETCH_ASSOC);

        // Hitung skor CPMK per mahasiswa
        $studentCpmkMap = [];
        foreach ($assessRows as $ar) {
            $mId = (int)$ar['mahasiswa_id'];
            $cpId = (int)$ar['cpmk_id'];
            $w = max(0.01, (float)$ar['bobot']);
            $v = (float)$ar['nilai'];

            if (!isset($studentCpmkMap[$mId][$cpId])) {
                $studentCpmkMap[$mId][$cpId] = ['sum' => 0.0, 'weight' => 0.0];
            }
            $studentCpmkMap[$mId][$cpId]['sum'] += ($v * $w);
            $studentCpmkMap[$mId][$cpId]['weight'] += $w;
        }

        // 6. Hitung skor CPL untuk setiap mahasiswa
        $students = [];
        $totalTercapai = 0;
        $totalBelumTercapai = 0;
        $totalBelumDinilai = 0;
        $allStudentAvgScores = [];

        foreach ($studentRows as $s) {
            $mId = (int)$s['mhs_id'];
            $cplBreakdown = [];
            $scoresList = [];
            $allCplPassed = true;
            $hasAnyAssessment = false;

            foreach ($cpls as $cid => $cplMeta) {
                $cpmkSums = 0;
                $cpmkCount = 0;

                foreach ($cplMeta['cpmk_ids'] as $cpId) {
                    if (isset($studentCpmkMap[$mId][$cpId]) && $studentCpmkMap[$mId][$cpId]['weight'] > 0) {
                        $cpmkScore = $studentCpmkMap[$mId][$cpId]['sum'] / $studentCpmkMap[$mId][$cpId]['weight'];
                        $cpmkSums += $cpmkScore;
                        $cpmkCount++;
                    }
                }

                if ($cpmkCount > 0) {
                    $hasAnyAssessment = true;
                    $score = round($cpmkSums / $cpmkCount, 2);
                    $isPassed = $score >= $cplMeta['target'];
                    $scoresList[] = $score;

                    if ($isPassed) {
                        $status = 'Tercapai';
                        $cpls[$cid]['passed_count']++;
                    } else {
                        $status = 'Belum Tercapai';
                        $allCplPassed = false;
                    }

                    $cpls[$cid]['score_sum'] += $score;
                    $cpls[$cid]['assessed_count']++;

                    $cplBreakdown[$cid] = [
                        'cpl_id' => $cid,
                        'kode' => $cplMeta['kode'],
                        'target' => $cplMeta['target'],
                        'score' => $score,
                        'is_measured' => true,
                        'status' => $status,
                        'status_badge' => $isPassed ? 'success' : 'danger',
                    ];
                } else {
                    $allCplPassed = false;
                    $cplBreakdown[$cid] = [
                        'cpl_id' => $cid,
                        'kode' => $cplMeta['kode'],
                        'target' => $cplMeta['target'],
                        'score' => null,
                        'is_measured' => false,
                        'status' => 'Belum Dinilai',
                        'status_badge' => 'secondary',
                    ];
                }
            }

            if (!$hasAnyAssessment) {
                $overallStatus = 'Belum Dinilai';
                $overallBadge = 'secondary';
                $totalBelumDinilai++;
                $overallScore = null;
            } elseif ($allCplPassed && count($cpls) > 0) {
                $overallStatus = 'Tercapai';
                $overallBadge = 'success';
                $totalTercapai++;
                $overallScore = count($scoresList) > 0 ? round(array_sum($scoresList) / count($scoresList), 2) : 0;
                $allStudentAvgScores[] = $overallScore;
            } else {
                $overallStatus = 'Belum Tercapai';
                $overallBadge = 'danger';
                $totalBelumTercapai++;
                $overallScore = count($scoresList) > 0 ? round(array_sum($scoresList) / count($scoresList), 2) : 0;
                $allStudentAvgScores[] = $overallScore;
            }

            $s['cpl_breakdown'] = $cplBreakdown;
            $s['overall_score'] = $overallScore;
            $s['overall_status'] = $overallStatus;
            $s['overall_badge'] = $overallBadge;
            $s['has_assessment'] = $hasAnyAssessment;

            $students[] = $s;
        }

        // Filter status mahasiswa jika dipilih
        if ($statusFilter !== '') {
            $students = array_values(array_filter($students, function ($st) use ($statusFilter) {
                if ($statusFilter === 'tercapai') return $st['overall_status'] === 'Tercapai';
                if ($statusFilter === 'belum_tercapai') return $st['overall_status'] === 'Belum Tercapai';
                if ($statusFilter === 'belum_dinilai') return $st['overall_status'] === 'Belum Dinilai';
                return true;
            }));
        }

        // Hitung agregasi per CPL
        foreach ($cpls as &$cp) {
            $cnt = $cp['assessed_count'];
            $cp['avg_score'] = $cnt > 0 ? round($cp['score_sum'] / $cnt, 2) : 0;
            $cp['pass_rate'] = $cnt > 0 ? round(($cp['passed_count'] / $cnt) * 100, 1) : 0;
            $cp['status'] = $cnt === 0 ? 'Belum Diukur' : ($cp['avg_score'] >= $cp['target'] ? 'Tercapai' : 'Belum Tercapai');
            unset($cp['score_sum']);
        }
        unset($cp);

        $totalAssessed = $totalTercapai + $totalBelumTercapai;
        $kpi = [
            'total_students' => count($studentRows),
            'displayed_students' => count($students),
            'assessed_students' => $totalAssessed,
            'passed_students' => $totalTercapai,
            'failed_students' => $totalBelumTercapai,
            'unassessed_students' => $totalBelumDinilai,
            'pass_rate' => $totalAssessed > 0 ? round(($totalTercapai / $totalAssessed) * 100, 1) : 0,
            'avg_score' => count($allStudentAvgScores) > 0 ? round(array_sum($allStudentAvgScores) / count($allStudentAvgScores), 2) : 0,
        ];

        return [
            'course' => $course,
            'cpls' => array_values($cpls),
            'classes' => $classes,
            'students' => $students,
            'kpi' => $kpi,
        ];
    }
}
