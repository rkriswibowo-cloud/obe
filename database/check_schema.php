<?php
require __DIR__ . '/../config/database.php';
$db = Database::getInstance();

echo "=== CPL TABLE ===\n";
$cols = $db->query('DESCRIBE cpl')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Key'] . "\n";

echo "\n=== MATA_KULIAH TABLE ===\n";
$cols = $db->query('DESCRIBE mata_kuliah')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Key'] . "\n";

echo "\n=== CPMK TABLE ===\n";
$cols = $db->query('DESCRIBE cpmk')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Key'] . "\n";

echo "\n=== CPL sample (first 5) ===\n";
$rows = $db->query('SELECT id, kode, kurikulum_id FROM cpl ORDER BY id LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  ID:{$r['id']} Kode:{$r['kode']} Kurikulum:{$r['kurikulum_id']}\n";
