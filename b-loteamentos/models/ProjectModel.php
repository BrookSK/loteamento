<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

final class ProjectModel extends Model
{
    public function listAll(): array
    {
        $sql = 'SELECT id, name, location, status, cover_image, created_at, updated_at FROM projects ORDER BY id DESC';
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function getById(int $id): array|false
    {
        $sql = 'SELECT * FROM projects WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO projects (name, location, description, cover_image, original_image, status, created_by) VALUES (:name, :location, :description, :cover_image, :original_image, :status, :created_by)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'location' => $data['location'],
            'description' => $data['description'],
            'cover_image' => $data['cover_image'],
            'original_image' => $data['original_image'],
            'status' => $data['status'],
            'created_by' => $data['created_by'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateVectorData(int $id, string $svgFile, string $svgRaw): void
    {
        $sql = 'UPDATE projects SET svg_file = :svg_file, svg_raw = :svg_raw WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'svg_file' => $svgFile,
            'svg_raw' => $svgRaw,
        ]);
    }

    public function setStatus(int $id, string $status): void
    {
        $sql = 'UPDATE projects SET status = :status WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }
}
