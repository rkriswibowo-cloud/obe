<?php
class ProdiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM program_studi ORDER BY nama ASC");
        return $stmt->fetchAll();
    }
}
