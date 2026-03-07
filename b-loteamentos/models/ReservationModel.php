<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

final class ReservationModel extends Model
{
    public function getActiveByLot(int $lotId): array|false
    {
        $sql = "SELECT * FROM reservations WHERE lot_id = :lot_id AND status = 'active' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lot_id' => $lotId]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO reservations (lot_id, corretor_id, buyer_name, buyer_phone, buyer_email, expires_at, status, notes)
                VALUES (:lot_id, :corretor_id, :buyer_name, :buyer_phone, :buyer_email, :expires_at, :status, :notes)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'lot_id' => $data['lot_id'],
            'corretor_id' => $data['corretor_id'],
            'buyer_name' => $data['buyer_name'] ?? null,
            'buyer_phone' => $data['buyer_phone'] ?? null,
            'buyer_email' => $data['buyer_email'] ?? null,
            'expires_at' => $data['expires_at'],
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $sql = 'UPDATE reservations SET status = :status WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public function getById(int $id): array|false
    {
        $sql = 'SELECT * FROM reservations WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
