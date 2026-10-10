<?php

class AccessContext
{
    private PDO $db;
    private int $userId;
    private string $role;
    private array $managedProdiIds = [];
    private ?int $userProdiId = null;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->userId = (int)($_SESSION['user_id'] ?? 0);
        $this->role = (string)($_SESSION['role'] ?? '');

        if ($this->userId > 0) {
            $userStmt = $this->db->prepare('SELECT prodi_id FROM users WHERE id = ?');
            $userStmt->execute([$this->userId]);
            $userProdiId = $userStmt->fetchColumn();
            $this->userProdiId = $userProdiId !== false && $userProdiId !== null ? (int)$userProdiId : null;

            $stmt = $this->db->prepare('SELECT id FROM program_studi WHERE kaprodi_user_id = ? AND status = ? ORDER BY id');
            $stmt->execute([$this->userId, 'ACTIVE']);
            $this->managedProdiIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
            if ($this->managedProdiIds === [] && $this->role === 'Kaprodi') {
                $stmt = $this->db->prepare('SELECT prodi_id FROM users WHERE id = ? AND prodi_id IS NOT NULL');
                $stmt->execute([$this->userId]);
                $legacyProdiId = $stmt->fetchColumn();
                if ($legacyProdiId !== false) $this->managedProdiIds = [(int)$legacyProdiId];
            }
        }
    }

    public function userId(): int { return $this->userId; }
    public function role(): string { return $this->role; }
    public function isSuperAdmin(): bool { return $this->role === 'Super Admin'; }
    public function isDosen(): bool { return $this->role === 'Dosen'; }
    public function isKaprodi(): bool { return $this->managedProdiIds !== [] || $this->role === 'Kaprodi'; }
    public function managedProdiIds(): array { return $this->managedProdiIds; }
    public function primaryManagedProdiId(): ?int { return $this->managedProdiIds[0] ?? null; }
    public function userProdiId(): ?int { return $this->userProdiId; }

    public function canAccessTeachingProdi($prodiId): bool
    {
        if ($this->isSuperAdmin()) return true;
        $prodiId = (int)$prodiId;
        return ($this->userProdiId !== null && $prodiId === $this->userProdiId)
            || in_array($prodiId, $this->managedProdiIds, true);
    }

    public function canAccessTeachingKelas($kelasId): bool
    {
        if ($this->isSuperAdmin()) return true;
        $stmt = $this->db->prepare('SELECT k.prodi_id FROM kelas kl JOIN mata_kuliah mk ON mk.id = kl.mata_kuliah_id JOIN kurikulum k ON k.id = mk.kurikulum_id WHERE kl.id = ?');
        $stmt->execute([(int)$kelasId]);
        $prodiId = $stmt->fetchColumn();
        return $prodiId !== false && $this->canAccessTeachingProdi((int)$prodiId);
    }

    public function assertTeachingKelas($kelasId): void
    {
        if (!$this->canAccessTeachingKelas($kelasId)) $this->forbidden();
    }

    public function filterProdiRows(array $rows): array
    {
        if ($this->isSuperAdmin()) return $rows;
        return array_values(array_filter($rows, fn($row) => $this->canAccessProdi($row['id'] ?? 0)));
    }

    public function filterKurikulumRows(array $rows): array
    {
        if ($this->isSuperAdmin()) return $rows;
        return array_values(array_filter($rows, fn($row) => $this->canAccessProdi($row['prodi_id'] ?? 0)));
    }

    public function canAccessProdi($prodiId): bool
    {
        if ($this->isSuperAdmin()) return true;
        return in_array((int)$prodiId, $this->managedProdiIds, true);
    }

    public function canAccessKurikulum($kurikulumId): bool
    {
        return $this->canAccessLinked('SELECT prodi_id FROM kurikulum WHERE id = ?', $kurikulumId);
    }

    public function canAccessCpl($cplId): bool
    {
        return $this->canAccessLinked('SELECT k.prodi_id FROM cpl c JOIN kurikulum k ON k.id = c.kurikulum_id WHERE c.id = ?', $cplId);
    }

    public function canAccessMataKuliah($mataKuliahId): bool
    {
        return $this->canAccessLinked('SELECT k.prodi_id FROM mata_kuliah mk JOIN kurikulum k ON k.id = mk.kurikulum_id WHERE mk.id = ?', $mataKuliahId);
    }

    public function canAccessKelas($kelasId): bool
    {
        return $this->canAccessLinked('SELECT k.prodi_id FROM kelas kl JOIN mata_kuliah mk ON mk.id = kl.mata_kuliah_id JOIN kurikulum k ON k.id = mk.kurikulum_id WHERE kl.id = ?', $kelasId);
    }

    public function canAccessMahasiswa($mahasiswaId): bool
    {
        return $this->canAccessLinked('SELECT prodi_id FROM users WHERE id = ?', $mahasiswaId);
    }

    public function canAccessRplProfile($profileId): bool
    {
        return $this->canAccessLinked('SELECT k.prodi_id FROM rpl_mahasiswa rm JOIN kurikulum k ON k.id = rm.kurikulum_id WHERE rm.id = ?', $profileId);
    }

    public function assertProdi($prodiId): void
    {
        if (!$this->canAccessProdi($prodiId)) $this->forbidden();
    }

    public function assertKurikulum($kurikulumId): void
    {
        if (!$this->canAccessKurikulum($kurikulumId)) $this->forbidden();
    }

    public function assertCpl($cplId): void
    {
        if (!$this->canAccessCpl($cplId)) $this->forbidden();
    }

    public function assertMataKuliah($mataKuliahId): void
    {
        if (!$this->canAccessMataKuliah($mataKuliahId)) $this->forbidden();
    }

    public function assertKelas($kelasId): void
    {
        if (!$this->canAccessKelas($kelasId)) $this->forbidden();
    }

    public function assertMahasiswa($mahasiswaId): void
    {
        if (!$this->canAccessMahasiswa($mahasiswaId)) $this->forbidden();
    }

    public function assertRplProfile($profileId): void
    {
        if (!$this->canAccessRplProfile($profileId)) $this->forbidden();
    }

    public function forbidden(): void
    {
        http_response_code(403);
        exit('403 Forbidden: Data berada di luar kewenangan program studi Anda.');
    }

    private function canAccessLinked(string $sql, $id): bool
    {
        if ($this->isSuperAdmin()) return true;
        if ((int)$id <= 0) return false;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);
        $prodiId = $stmt->fetchColumn();
        return $prodiId !== false && $this->canAccessProdi((int)$prodiId);
    }
}
