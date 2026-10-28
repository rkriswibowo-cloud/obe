<?php
// config/app.php
$detectedBase = '';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (strpos($scriptName, '/obe/public') !== false) {
    $detectedBase = '/obe/public';
} elseif (strpos($scriptName, '/obe') !== false) {
    $detectedBase = '/obe';
}

return [
    'app_name' => 'SIP-OKE : Sistem Informasi Pengukuran Outcome Kompetensi dan Evaluasi',
    'app_short_name' => 'SIP-OKE',
    'base_url' => $detectedBase,
    'debug' => true
];
