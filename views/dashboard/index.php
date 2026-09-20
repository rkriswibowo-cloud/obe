<?php
$title = 'Dashboard - Sistem Informasi OBE & Pengukuran CPL';
ob_start();
$baseUrl = $GLOBALS['config']['base_url'] ?? '/obe/public';
$avgCpl = isset($stats['avg_cpl']) ? floatval($stats['avg_cpl']) : 0;
?>

<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Welcome & Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="margin: 0; font-size: 1.6rem; font-weight: 700; color: var(--text-main);">
                <i class="fa-solid fa-graduation-cap" style="color: var(--primary); margin-right: 8px;"></i>Dashboard Sistem OBE
            </h2>
            <p style="color: var(--text-muted); margin: 6px 0 0 0; font-size: 0.92rem;">
                Platform Penjaminan Mutu Pembelajaran & Pengukuran Ketercapaian Capaian Pembelajaran Lulusan (CPL).
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="<?= $baseUrl ?>/laporan" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 9px 16px;">
                <i class="fa-solid fa-chart-radar"></i> Radar Chart CPL
            </a>
            <a href="<?= $baseUrl ?>/cqi" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; padding: 9px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border);">
                <i class="fa-solid fa-arrows-rotate"></i> Siklus CQI
            </a>
        </div>
    </div>
    
    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;">
        <div style="background: var(--surface); padding: 22px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Total CPL Terdaftar</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary); margin-top: 4px; line-height: 1;">
                    <?= isset($stats['total_cpl']) ? $stats['total_cpl'] : 0 ?>
                </div>
                <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 6px;">Standar Capaian Lulusan</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.4rem;">
                <i class="fa-solid fa-bullseye"></i>
            </div>
        </div>

        <div style="background: var(--surface); padding: 22px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Mata Kuliah (MK)</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--secondary); margin-top: 4px; line-height: 1;">
                    <?= isset($stats['total_mk']) ? $stats['total_mk'] : 0 ?>
                </div>
                <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 6px;">Terdaftar di Kurikulum</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.12); display: flex; align-items: center; justify-content: center; color: var(--secondary); font-size: 1.4rem;">
                <i class="fa-solid fa-book-open"></i>
            </div>
        </div>

        <div style="background: var(--surface); padding: 22px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">MK dgn RPS Aktif</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--warning); margin-top: 4px; line-height: 1;">
                    <?= isset($stats['rps_aktif']) ? $stats['rps_aktif'] : 0 ?>
                </div>
                <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 6px;">Memiliki CPMK & RPS</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); display: flex; align-items: center; justify-content: center; color: var(--warning); font-size: 1.4rem;">
                <i class="fa-solid fa-file-contract"></i>
            </div>
        </div>

        <div style="background: var(--surface); padding: 22px; border-radius: var(--radius-md); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Rata-rata Capaian CPL</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: <?= $avgCpl >= 70 ? 'var(--success)' : ($avgCpl > 0 ? 'var(--warning)' : 'var(--text-muted)') ?>; margin-top: 4px; line-height: 1;">
                    <?= $avgCpl ?>%
                </div>
                <div style="color: <?= $avgCpl >= 70 ? 'var(--success)' : ($avgCpl > 0 ? 'var(--warning)' : 'var(--text-muted)') ?>; font-size: 0.8rem; margin-top: 6px;">
                    <i class="fa-solid <?= $avgCpl >= 70 ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i> 
                    <?= $avgCpl >= 70 ? 'Memenuhi Standar (≥70%)' : ($avgCpl > 0 ? 'Di Bawah Target (<70%)' : 'Belum Terukur') ?>
                </div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: <?= $avgCpl >= 70 ? 'rgba(16, 185, 129, 0.12)' : 'rgba(239, 68, 68, 0.12)' ?>; display: flex; align-items: center; justify-content: center; color: <?= $avgCpl >= 70 ? 'var(--success)' : 'var(--danger)' ?>; font-size: 1.4rem;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>

    <!-- MAIN CARD: ALUR SISTEM PENGUKURAN CPL -->
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
        
        <!-- Card Header with Gradient Accent -->
        <div style="padding: 24px 28px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(59, 130, 246, 0.04) 100%); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 1.6rem; box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h3 style="margin: 0; font-size: 1.35rem; font-weight: 700; color: var(--text-main);">
                            Alur Sistem Pengukuran Ketercapaian CPL
                        </h3>
                        <span style="background: rgba(99, 102, 241, 0.18); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.35); font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 9999px; letter-spacing: 0.03em;">
                            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> OBE Pipeline 100% PRD
                        </span>
                    </div>
                    <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 0.9rem;">
                        Peta penjaminan mutu berkesinambungan: dari perancangan kurikulum, asesmen berbobot, kalkulasi berjenjang, hingga tindak lanjut CQI.
                    </p>
                </div>
            </div>
            <div>
                <a href="<?= $baseUrl ?>/pemetaan-mk-cpl" class="btn btn-sm btn-outline" style="border: 1px solid var(--border); background: var(--surface-hover); color: var(--text-main); font-size: 0.85rem; padding: 7px 14px; border-radius: var(--radius-sm); display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-table"></i> Matriks Pemetaan
                </a>
            </div>
        </div>

        <!-- Golden Architectural Rule Callout -->
        <div style="padding: 18px 28px; background: rgba(15, 17, 26, 0.65); border-bottom: 1px solid var(--border);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="color: var(--primary); font-size: 1.1rem; margin-top: 2px;"><i class="fa-solid fa-bullseye"></i></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #c7d2fe;">1. Master Kurikulum</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                            Menentukan <strong>"apa yang harus dicapai"</strong>: MK &rarr; CPL &rarr; CPMK &rarr; Sub-CPMK &rarr; Indikator &rarr; Rubrik.
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="color: var(--warning); font-size: 1.1rem; margin-top: 2px;"><i class="fa-solid fa-calendar-week"></i></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #fde68a;">2. RPS 16 Pertemuan</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                            Menentukan <strong>"bagaimana pembelajaran dilaksanakan"</strong>: Rencana mingguan, materi, pustaka, UTS & UAS.
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="color: var(--secondary); font-size: 1.1rem; margin-top: 2px;"><i class="fa-solid fa-pen-ruler"></i></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #93c5fd;">3. Asesmen Berbasis Rubrik</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                            Menghasilkan <strong>"bukti pencapaian"</strong>: Nilai kuis, tugas, praktikum, ujian berbasis deskriptor mutu.
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="color: var(--success); font-size: 1.1rem; margin-top: 2px;"><i class="fa-solid fa-calculator"></i></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #a7f3d0;">4. Mesin Pengukuran & CQI</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                            Menghitung <strong>"seberapa jauh CPL tercapai"</strong>: Agregasi skor berlapis, radar chart, dan siklus perbaikan.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Step-by-Step OBE Pipeline (5 Sequential Phases) -->
        <div style="padding: 28px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); gap: 18px; position: relative;">
                
                <!-- PHASE 1 -->
                <div class="obe-pipeline-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s ease;">
                    <div style="position: absolute; top: -10px; left: 16px; background: var(--primary); color: #fff; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                        Fase 1
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px; margin-bottom: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.15); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fa-solid fa-book-bookmark"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main);">Desain & Kurikulum</h4>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Pondasi Target Lulusan</div>
                            </div>
                        </div>

                        <!-- Sub-steps -->
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8rem;">
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #818cf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">1</span>
                                <div><strong style="color: var(--text-main);">Kurikulum</strong>: Penetapan standar profil prodi.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #818cf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">2</span>
                                <div><strong style="color: var(--text-main);">Mata Kuliah (MK)</strong>: Distribusi SKS & semester.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #818cf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">3</span>
                                <div><strong style="color: var(--text-main);">CPL Prodi</strong>: Sikap, Pengetahuan, & Keterampilan.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #818cf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">4</span>
                                <div><strong style="color: var(--text-main);">Matriks MK-CPL</strong>: Pemetaan bobot CPL ke MK.</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 16px; pt: 12px; border-top: 1px dashed var(--border);">
                        <a href="<?= $baseUrl ?>/pemetaan-mk-cpl" style="font-size: 0.78rem; font-weight: 600; color: var(--primary); display: inline-flex; align-items: center; gap: 6px;">
                            Buka Matriks MK-CPL <i class="fa-solid fa-arrow-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>

                <!-- PHASE 2 -->
                <div class="obe-pipeline-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s ease;">
                    <div style="position: absolute; top: -10px; left: 16px; background: #0ea5e9; color: #fff; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                        Fase 2
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px; margin-bottom: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(14, 165, 233, 0.15); display: flex; align-items: center; justify-content: center; color: #38bdf8;">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main);">CPMK & Rubrik</h4>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Instrumen Kriteria Mutu</div>
                            </div>
                        </div>

                        <!-- Sub-steps -->
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8rem;">
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #38bdf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">5</span>
                                <div><strong style="color: var(--text-main);">CPMK</strong>: Capaian belajar spesifik per mata kuliah.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #38bdf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">6</span>
                                <div><strong style="color: var(--text-main);">Sub-CPMK</strong>: Kemampuan akhir tiap tahapan ajar.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #38bdf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">7</span>
                                <div><strong style="color: var(--text-main);">Indikator</strong>: Kriteria keterukuran pencapaian.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #38bdf8; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">8</span>
                                <div><strong style="color: var(--text-main);">Rubrik Mutu</strong>: Deskriptor analitik & batas kelulusan.</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 16px; pt: 12px; border-top: 1px dashed var(--border);">
                        <a href="<?= $baseUrl ?>/rubrik" style="font-size: 0.78rem; font-weight: 600; color: #38bdf8; display: inline-flex; align-items: center; gap: 6px;">
                            Buka Rubrik Builder <i class="fa-solid fa-arrow-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>

                <!-- PHASE 3 -->
                <div class="obe-pipeline-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s ease;">
                    <div style="position: absolute; top: -10px; left: 16px; background: var(--warning); color: #000; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                        Fase 3
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px; margin-bottom: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: var(--warning);">
                                <i class="fa-solid fa-file-contract"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main);">RPS & Asesmen</h4>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Pelaksanaan Perkuliahan</div>
                            </div>
                        </div>

                        <!-- Sub-steps -->
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8rem;">
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #fbbf24; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">9</span>
                                <div><strong style="color: var(--text-main);">RPS (1-16 Pekan)</strong>: Aktivitas mingguan, UTS & UAS.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #fbbf24; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">10</span>
                                <div><strong style="color: var(--text-main);">Kelas Kuliah</strong>: Dosen pengampu & peserta kelas.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #fbbf24; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">11</span>
                                <div><strong style="color: var(--text-main);">Instrumen Uji</strong>: Tugas, Kuis, Proyek, Praktikum.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #fbbf24; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">12</span>
                                <div><strong style="color: var(--text-main);">Skor Mahasiswa</strong>: Bukti autentik asesmen.</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 16px; pt: 12px; border-top: 1px dashed var(--border); display: flex; justify-content: space-between;">
                        <a href="<?= $baseUrl ?>/rps" style="font-size: 0.78rem; font-weight: 600; color: var(--warning); display: inline-flex; align-items: center; gap: 4px;">
                            RPS <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i>
                        </a>
                        <a href="<?= $baseUrl ?>/asesmen" style="font-size: 0.78rem; font-weight: 600; color: var(--warning); display: inline-flex; align-items: center; gap: 4px;">
                            Asesmen <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i>
                        </a>
                    </div>
                </div>

                <!-- PHASE 4 -->
                <div class="obe-pipeline-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s ease;">
                    <div style="position: absolute; top: -10px; left: 16px; background: #8b5cf6; color: #fff; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                        Fase 4
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px; margin-bottom: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(139, 92, 246, 0.15); display: flex; align-items: center; justify-content: center; color: #a78bfa;">
                                <i class="fa-solid fa-calculator"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main);">Kalkulasi Berlapis</h4>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Mesin Agregasi Terbobot</div>
                            </div>
                        </div>

                        <!-- Sub-steps -->
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8rem;">
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #c4b5fd; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">13</span>
                                <div><strong style="color: var(--text-main);">Nilai Sub-CPMK</strong>: Agregasi skor asesmen.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #c4b5fd; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">14</span>
                                <div><strong style="color: var(--text-main);">Nilai CPMK</strong>: Penjumlahan terbobot Sub-CPMK.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #c4b5fd; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">15</span>
                                <div><strong style="color: var(--text-main);">CPL per MK</strong>: Kontribusi nilai pada MK itu.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #c4b5fd; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">16</span>
                                <div><strong style="color: var(--text-main);">Portofolio Mhs</strong>: Kumulatif seluruh mata kuliah.</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 16px; pt: 12px; border-top: 1px dashed var(--border);">
                        <a href="<?= $baseUrl ?>/laporan" style="font-size: 0.78rem; font-weight: 600; color: #a78bfa; display: inline-flex; align-items: center; gap: 6px;">
                            Lihat Portofolio CPL <i class="fa-solid fa-arrow-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>

                <!-- PHASE 5 -->
                <div class="obe-pipeline-card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s ease;">
                    <div style="position: absolute; top: -10px; left: 16px; background: var(--success); color: #fff; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                        Fase 5
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px; margin-bottom: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: var(--success);">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-main);">Radar Chart & CQI</h4>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Siklus Peningkatan Mutu</div>
                            </div>
                        </div>

                        <!-- Sub-steps -->
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.8rem;">
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #6ee7b7; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">17</span>
                                <div><strong style="color: var(--text-main);">Radar Chart</strong>: Visualisasi profil CPL angkatan.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #6ee7b7; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">18</span>
                                <div><strong style="color: var(--text-main);">Ambang Batas</strong>: Target ketercapaian prodi &ge; 70%.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #6ee7b7; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">19</span>
                                <div><strong style="color: var(--text-main);">Analisis Defisit</strong>: Identifikasi CPL di bawah target.</div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                <span style="background: var(--surface-hover); color: #6ee7b7; font-weight: 700; font-size: 0.7rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%;">20</span>
                                <div><strong style="color: var(--text-main);">Siklus CQI</strong>: Open &rarr; Progress &rarr; Re-measured.</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 16px; pt: 12px; border-top: 1px dashed var(--border);">
                        <a href="<?= $baseUrl ?>/cqi" style="font-size: 0.78rem; font-weight: 600; color: var(--success); display: inline-flex; align-items: center; gap: 6px;">
                            Buka Tindak Lanjut CQI <i class="fa-solid fa-arrow-right" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Traceability & Audit Trail Callout Box -->
        <div style="margin: 0 28px 28px 28px; padding: 20px; border-radius: var(--radius-md); background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.2); display: flex; gap: 16px; align-items: flex-start;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99, 102, 241, 0.2); display: flex; align-items: center; justify-content: center; color: #a5b4fc; font-size: 1.2rem; flex-shrink: 0;">
                <i class="fa-solid fa-magnifying-glass-chart"></i>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h5 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #c7d2fe;">
                        Prinsip Traceability & Integritas Pengukuran (Audit Trail OBE)
                    </h5>
                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; background: rgba(16, 185, 129, 0.15); color: #34d399; font-weight: 600;">
                        PRD Section 3.1
                    </span>
                </div>
                <p style="margin: 6px 0 0 0; font-size: 0.84rem; color: var(--text-muted); line-height: 1.6;">
                    <strong style="color: #e2e8f0;">Nilai akhir mata kuliah (A, B, C) tidak otomatis menjadi nilai CPL.</strong> Nilai CPL diperoleh murni dari bukti asesmen yang dipetakan presisi terhadap Sub-CPMK dan dinilai dengan rubrik. Sistem menjamin penelusuran balik akuntabel:
                </p>
                <div style="margin-top: 8px; padding: 10px 14px; background: rgba(15, 17, 26, 0.85); border-radius: var(--radius-sm); border-left: 3px solid var(--primary); font-family: monospace; font-size: 0.8rem; color: #e0e7ff;">
                    "Skor CPL Mahasiswa = 78 &rarr; Berasal dari MK apa &rarr; CPMK apa &rarr; Sub-CPMK apa &rarr; Instrumen Asesmen apa &rarr; Skor Rubrik apa?"
                </div>
            </div>
        </div>

        <!-- Quick Access Module Navigation Bar -->
        <div style="padding: 16px 28px; background: var(--surface-hover); border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">
                <i class="fa-solid fa-compass" style="margin-right: 6px;"></i> Akses Cepat Modul OBE:
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="<?= $baseUrl ?>/kurikulum" class="badge-link"><i class="fa-solid fa-book"></i> Kurikulum</a>
                <a href="<?= $baseUrl ?>/cpl" class="badge-link"><i class="fa-solid fa-bullseye"></i> CPL</a>
                <a href="<?= $baseUrl ?>/pemetaan-mk-cpl" class="badge-link"><i class="fa-solid fa-link"></i> Matriks Pemetaan</a>
                <a href="<?= $baseUrl ?>/rubrik" class="badge-link"><i class="fa-solid fa-table-cells"></i> Rubrik</a>
                <a href="<?= $baseUrl ?>/rps" class="badge-link"><i class="fa-solid fa-file-contract"></i> RPS 1-16</a>
                <a href="<?= $baseUrl ?>/asesmen" class="badge-link"><i class="fa-solid fa-check-to-slot"></i> Asesmen</a>
                <a href="<?= $baseUrl ?>/laporan" class="badge-link"><i class="fa-solid fa-chart-pie"></i> Laporan CPL</a>
                <a href="<?= $baseUrl ?>/cqi" class="badge-link"><i class="fa-solid fa-arrows-rotate"></i> Siklus CQI</a>
            </div>
        </div>

    </div>

</div>

<style>
.obe-pipeline-card:hover {
    transform: translateY(-3px);
    border-color: rgba(99, 102, 241, 0.45) !important;
    background: rgba(255, 255, 255, 0.04) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}

.badge-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-muted);
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    text-decoration: none;
    transition: all 0.2s ease;
}

.badge-link:hover {
    color: #ffffff;
    background: var(--primary);
    border-color: var(--primary);
    text-decoration: none;
    transform: translateY(-1px);
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
