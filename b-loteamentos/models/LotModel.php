<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

final class LotModel extends Model
{
    public function listByProject(int $projectId): array
    {
        $sql = 'SELECT * FROM lots WHERE project_id = :project_id ORDER BY id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['project_id' => $projectId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function getByProjectAndPolygon(int $projectId, string $polygonId): array|false
    {
        $sql = 'SELECT * FROM lots WHERE project_id = :project_id AND polygon_id = :polygon_id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'project_id' => $projectId,
            'polygon_id' => $polygonId,
        ]);
        return $stmt->fetch();
    }

    public function create(int $projectId, string $polygonId, array $data): int
    {
        $sql = 'INSERT INTO lots (project_id, polygon_id, numero_lote, quadra, area_m2, frente_m, fundo_m, lateral_esq_m, lateral_dir_m, valor, status, observacoes)
                VALUES (:project_id, :polygon_id, :numero_lote, :quadra, :area_m2, :frente_m, :fundo_m, :lateral_esq_m, :lateral_dir_m, :valor, :status, :observacoes)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'project_id' => $projectId,
            'polygon_id' => $polygonId,
            'numero_lote' => $data['numero_lote'] ?? null,
            'quadra' => $data['quadra'] ?? null,
            'area_m2' => $data['area_m2'] ?? null,
            'frente_m' => $data['frente_m'] ?? null,
            'fundo_m' => $data['fundo_m'] ?? null,
            'lateral_esq_m' => $data['lateral_esq_m'] ?? null,
            'lateral_dir_m' => $data['lateral_dir_m'] ?? null,
            'valor' => $data['valor'] ?? null,
            'status' => $data['status'] ?? 'disponivel',
            'observacoes' => $data['observacoes'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $lotId, array $data): void
    {
        $sql = 'UPDATE lots SET
                    numero_lote = :numero_lote,
                    quadra = :quadra,
                    area_m2 = :area_m2,
                    frente_m = :frente_m,
                    fundo_m = :fundo_m,
                    lateral_esq_m = :lateral_esq_m,
                    lateral_dir_m = :lateral_dir_m,
                    valor = :valor,
                    status = :status,
                    observacoes = :observacoes
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $lotId,
            'numero_lote' => $data['numero_lote'] ?? null,
            'quadra' => $data['quadra'] ?? null,
            'area_m2' => $data['area_m2'] ?? null,
            'frente_m' => $data['frente_m'] ?? null,
            'fundo_m' => $data['fundo_m'] ?? null,
            'lateral_esq_m' => $data['lateral_esq_m'] ?? null,
            'lateral_dir_m' => $data['lateral_dir_m'] ?? null,
            'valor' => $data['valor'] ?? null,
            'status' => $data['status'] ?? 'disponivel',
            'observacoes' => $data['observacoes'] ?? null,
        ]);
    }
}
