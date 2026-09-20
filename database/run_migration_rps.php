<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

$sql = file_get_contents(__DIR__ . '/rps_schema.sql');
$db->exec($sql);
echo "Migration rps_schema.sql executed successfully.\n";
