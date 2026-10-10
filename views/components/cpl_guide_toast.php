<?php
// views/components/cpl_guide_toast.php
// Toast Card Panduan Langkah-Langkah Penggunaan Sistem Pengukuran CPL

$autoShowToast = false;
if (isset($_SESSION['just_logged_in'])) {
    $autoShowToast = true;
    unset($_SESSION['just_logged_in']);
}
?>

<!-- CPL Guide Floating Toast Card Component -->
<div id="cplGuideToastContainer" class="cpl-guide-toast-wrapper" style="display: none;">
    <div class="cpl-guide-card">
        <!-- Toast Header -->
        <div class="cpl-guide-header">
            <div class="cpl-guide-title-box">
                <div class="cpl-guide-icon-badge">
                    <i class="fa-solid fa-route"></i>
                </div>
                <div>
                    <div class="cpl-guide-badge">
                        <span class="pulse-dot"></span> Panduan Pengukuran CPL
                    </div>
                    <h4 class="cpl-guide-title">Langkah Penggunaan Sistem OBE</h4>
                </div>
            </div>
            <button type="button" class="cpl-guide-close-btn" onclick="closeCplGuideToast()" title="Tutup Panduan">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Toast Body: Steps 1 to 5 -->
        <div class="cpl-guide-body">
            <p class="cpl-guide-intro">
                Ikuti 5 tahapan terstruktur berikut untuk memastikan perhitungan ketercapaian <strong>Capaian Pembelajaran Lulusan (CPL)</strong> berjalan akurat:
            </p>

            <div class="cpl-steps-list">
                <!-- Step 1 -->
                <div class="cpl-step-item">
                    <div class="cpl-step-num step-1">1</div>
                    <div class="cpl-step-content">
                        <div class="cpl-step-head">
                            <strong>Setup Profil Lulusan & CPL</strong>
                            <a href="<?= $GLOBALS['config']['base_url'] ?? '/obe/public' ?>/cpl" class="cpl-step-link" title="Buka Menu CPL"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        </div>
                        <p>Tetapkan Profil Lulusan (PL) dan rumusan Capaian Pembelajaran Lulusan pada kurikulum aktif.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="cpl-step-item">
                    <div class="cpl-step-num step-2">2</div>
                    <div class="cpl-step-content">
                        <div class="cpl-step-head">
                            <strong>Pemetaan Matriks MK ke CPL</strong>
                            <a href="<?= $GLOBALS['config']['base_url'] ?? '/obe/public' ?>/pemetaan-mk-cpl" class="cpl-step-link" title="Buka Matriks"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        </div>
                        <p>Hubungkan setiap Mata Kuliah (MK) ke CPL yang dibebankan serta tentukan target bobot kontribusinya.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="cpl-step-item">
                    <div class="cpl-step-num step-3">3</div>
                    <div class="cpl-step-content">
                        <div class="cpl-step-head">
                            <strong>RPS, CPMK, Sub-CPMK & Rubrik</strong>
                            <a href="<?= $GLOBALS['config']['base_url'] ?? '/obe/public' ?>/rps" class="cpl-step-link" title="Buka RPS"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        </div>
                        <p>Dosen menyusun Rencana Pembelajaran Semester, menurunkan CPL ke CPMK/Sub-CPMK, dan membuat rubrik asesmen.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="cpl-step-item">
                    <div class="cpl-step-num step-4">4</div>
                    <div class="cpl-step-content">
                        <div class="cpl-step-head">
                            <strong>Input Nilai Asesmen Mahasiswa</strong>
                            <a href="<?= $GLOBALS['config']['base_url'] ?? '/obe/public' ?>/asesmen" class="cpl-step-link" title="Buka Asesmen"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        </div>
                        <p>Lakukan penilaian kuis, tugas, UTS, dan UAS mahasiswa per kelas berdasarkan indikator Sub-CPMK.</p>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="cpl-step-item">
                    <div class="cpl-step-num step-5">5</div>
                    <div class="cpl-step-content">
                        <div class="cpl-step-head">
                            <strong>Pantau Radar CPL & Siklus CQI</strong>
                            <a href="<?= $GLOBALS['config']['base_url'] ?? '/obe/public' ?>/laporan" class="cpl-step-link" title="Buka Laporan"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        </div>
                        <p>Lihat grafik Radar Ketercapaian CPL, cetak KHS OBE, dan lakukan perbaikan berkelanjutan (CQI).</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Footer -->
        <div class="cpl-guide-footer">
            <label class="cpl-guide-remember">
                <input type="checkbox" id="chkDontShowGuideAgain">
                <span>Jangan tampilkan otomatis lagi</span>
            </label>
            <div class="cpl-guide-actions">
                <button type="button" class="btn-guide-detail" onclick="scrollToAlurCpl()">
                    <i class="fa-solid fa-diagram-project"></i> Detail Alur
                </button>
                <button type="button" class="btn-guide-close" onclick="closeCplGuideToast()">
                    Mengerti <i class="fa-solid fa-check"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Trigger Button (Always available to reopen guide) -->
<button type="button" id="btnOpenCplGuideTrigger" class="cpl-guide-trigger-pill" onclick="openCplGuideToast()" title="Buka Panduan Langkah Pengukuran CPL">
    <div class="cpl-trigger-icon">
        <i class="fa-solid fa-route"></i>
    </div>
    <span class="cpl-trigger-text">Panduan Alur CPL</span>
</button>

<style>
/* ==========================================================================
   CPL GUIDE TOAST CARD STYLING
   ========================================================================== */
.cpl-guide-toast-wrapper {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    max-width: 440px;
    width: calc(100vw - 36px);
    pointer-events: none;
}

.cpl-guide-card {
    pointer-events: auto;
    background: rgba(15, 17, 23, 0.94);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(99, 102, 241, 0.35);
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65), 0 0 25px rgba(99, 102, 241, 0.2);
    overflow: hidden;
    transform: translateY(20px) scale(0.96);
    opacity: 0;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    color: #f4f4f5;
}

[data-theme="light"] .cpl-guide-card {
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid rgba(99, 102, 241, 0.25);
    box-shadow: 0 25px 50px -12px rgba(99, 102, 241, 0.15), 0 10px 25px rgba(0, 0, 0, 0.08);
    color: #09090b;
}

.cpl-guide-toast-wrapper.show .cpl-guide-card {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* Header */
.cpl-guide-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.18) 0%, rgba(139, 92, 246, 0.08) 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}
[data-theme="light"] .cpl-guide-header {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.04) 100%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.cpl-guide-title-box {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cpl-guide-icon-badge {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    box-shadow: 0 6px 14px rgba(99, 102, 241, 0.4);
    flex-shrink: 0;
}

.cpl-guide-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #a5b4fc;
    margin-bottom: 2px;
}
[data-theme="light"] .cpl-guide-badge {
    color: #4f46e5;
}

.pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    animation: pulseGlow 1.8s infinite;
}

@keyframes pulseGlow {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.cpl-guide-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: inherit;
    line-height: 1.25;
}

.cpl-guide-close-btn {
    background: transparent;
    border: none;
    color: #a1a1aa;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s ease;
}
.cpl-guide-close-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}
[data-theme="light"] .cpl-guide-close-btn:hover {
    background: rgba(0, 0, 0, 0.06);
    color: #09090b;
}

/* Body */
.cpl-guide-body {
    padding: 16px 20px;
    max-height: 360px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.cpl-guide-intro {
    font-size: 0.8rem;
    color: #a1a1aa;
    margin: 0 0 14px 0;
    line-height: 1.45;
}
[data-theme="light"] .cpl-guide-intro {
    color: #52525b;
}

.cpl-steps-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
}

.cpl-step-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 10px 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.2s ease;
}
[data-theme="light"] .cpl-step-item {
    background: rgba(0, 0, 0, 0.02);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.cpl-step-item:hover {
    background: rgba(99, 102, 241, 0.08);
    border-color: rgba(99, 102, 241, 0.25);
    transform: translateX(3px);
}

.cpl-step-num {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 800;
    color: #ffffff;
    flex-shrink: 0;
    margin-top: 1px;
}
.step-1 { background: #6366f1; }
.step-2 { background: #0ea5e9; }
.step-3 { background: #f59e0b; }
.step-4 { background: #10b981; }
.step-5 { background: #ec4899; }

.cpl-step-content {
    flex: 1;
}

.cpl-step-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
    color: inherit;
}

.cpl-step-link {
    color: #818cf8;
    font-size: 0.75rem;
    text-decoration: none;
    opacity: 0.7;
    transition: opacity 0.2s;
}
.cpl-step-link:hover {
    opacity: 1;
    color: #a5b4fc;
}
[data-theme="light"] .cpl-step-link {
    color: #4f46e5;
}

.cpl-step-content p {
    margin: 3px 0 0 0;
    font-size: 0.74rem;
    color: #a1a1aa;
    line-height: 1.35;
}
[data-theme="light"] .cpl-step-content p {
    color: #64748b;
}

/* Footer */
.cpl-guide-footer {
    padding: 12px 20px;
    background: rgba(0, 0, 0, 0.25);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
[data-theme="light"] .cpl-guide-footer {
    background: rgba(0, 0, 0, 0.02);
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.cpl-guide-remember {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.72rem;
    color: #a1a1aa;
    cursor: pointer;
}
[data-theme="light"] .cpl-guide-remember {
    color: #64748b;
}

.cpl-guide-remember input {
    accent-color: #6366f1;
    cursor: pointer;
}

.cpl-guide-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-guide-detail {
    padding: 7px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 8px;
    border: 1px solid rgba(99, 102, 241, 0.35);
    background: rgba(99, 102, 241, 0.15);
    color: #a5b4fc;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}
.btn-guide-detail:hover {
    background: rgba(99, 102, 241, 0.28);
    color: #ffffff;
}
[data-theme="light"] .btn-guide-detail {
    color: #4f46e5;
    background: rgba(99, 102, 241, 0.08);
}
[data-theme="light"] .btn-guide-detail:hover {
    background: rgba(99, 102, 241, 0.15);
}

.btn-guide-close {
    padding: 7px 14px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    background: #6366f1;
    color: #ffffff;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.35);
    transition: all 0.2s ease;
}
.btn-guide-close:hover {
    background: #4f46e5;
    transform: translateY(-1px);
}

/* Floating Trigger Pill Button */
.cpl-guide-trigger-pill {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99998;
    background: rgba(18, 18, 24, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(99, 102, 241, 0.35);
    border-radius: 9999px;
    padding: 8px 16px 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 600;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 0 15px rgba(99, 102, 241, 0.2);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
[data-theme="light"] .cpl-guide-trigger-pill {
    background: rgba(255, 255, 255, 0.92);
    border-color: rgba(99, 102, 241, 0.3);
    color: #09090b;
    box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.2), 0 4px 12px rgba(0, 0, 0, 0.05);
}

.cpl-guide-trigger-pill:hover {
    transform: translateY(-2px);
    border-color: #6366f1;
    box-shadow: 0 14px 28px -5px rgba(99, 102, 241, 0.35);
}

.cpl-trigger-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}
</style>

<script>
    const CPL_GUIDE_STORAGE_KEY = 'cpl_guide_dont_show_auto';

    function openCplGuideToast() {
        const container = document.getElementById('cplGuideToastContainer');
        const trigger = document.getElementById('btnOpenCplGuideTrigger');
        if (container) {
            container.style.display = 'block';
            setTimeout(() => {
                container.classList.add('show');
            }, 20);
        }
        if (trigger) {
            trigger.style.display = 'none';
        }
    }

    function closeCplGuideToast() {
        const container = document.getElementById('cplGuideToastContainer');
        const trigger = document.getElementById('btnOpenCplGuideTrigger');
        const chk = document.getElementById('chkDontShowGuideAgain');

        if (chk && chk.checked) {
            localStorage.setItem(CPL_GUIDE_STORAGE_KEY, 'true');
        }

        if (container) {
            container.classList.remove('show');
            setTimeout(() => {
                container.style.display = 'none';
                if (trigger) trigger.style.display = 'flex';
            }, 300);
        }
    }

    function scrollToAlurCpl() {
        closeCplGuideToast();
        const alurSection = document.getElementById('alurCplSection');
        if (alurSection) {
            alurSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            alurSection.style.transition = 'box-shadow 0.4s ease';
            alurSection.style.boxShadow = '0 0 0 3px #6366f1, 0 8px 30px rgba(99, 102, 241, 0.3)';
            setTimeout(() => {
                alurSection.style.boxShadow = '';
            }, 2000);
        } else {
            // Jika bukan di halaman dashboard, navigasikan ke dashboard
            window.location.href = '<?= $GLOBALS['config']['base_url'] ?? '/obe/public' ?>/dashboard#alurCplSection';
        }
    }

    // Auto trigger on page load if user just logged in or if on dashboard for the first time
    document.addEventListener('DOMContentLoaded', function() {
        const shouldAutoShowBackend = <?= $autoShowToast ? 'true' : 'false' ?>;
        const isPermanentlyDismissed = localStorage.getItem(CPL_GUIDE_STORAGE_KEY) === 'true';

        if (shouldAutoShowBackend || (!isPermanentlyDismissed && window.location.pathname.endsWith('/dashboard'))) {
            setTimeout(() => {
                openCplGuideToast();
            }, 650);
        }
    });
</script>
