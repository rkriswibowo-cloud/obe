<?php
// app/Controllers/AuditLogController.php

require_once __DIR__ . '/../Models/AuditLogModel.php';

class AuditLogController {
    private $auditModel;

    public function __construct() {
        $this->auditModel = new AuditLogModel();
    }

    public function index() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $logs = $this->auditModel->getAll($limit);

        require __DIR__ . '/../../views/audit_log/index.php';
    }
}
