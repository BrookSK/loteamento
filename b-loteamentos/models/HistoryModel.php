<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

final class HistoryModel extends Model
{
    public function add(int $lotId, ?int $userId, string $action, array|null $oldValue, array|null $newValue): int
    {
        $sql = 'INSERT INTO lot_history (lot_id, user_id, action, old_value, new_value)
                VALUES (:lot_id, :user_id, :action, :old_value, :new_value)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'lot_id' => $lotId,
            'user_id' => $userId,
            'action' => $action,
            'old_value' => $oldValue === null ? null : json_encode($oldValue, JSON_UNESCAPED_UNICODE),
            'new_value' => $newValue === null ? null : json_encode($newValue, JSON_UNESCAPED_UNICODE),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function listByLot(int $lotId, int $limit = 50): array
    {
        $sql = 'SELECT * FROM lot_history WHERE lot_id = :lot_id ORDER BY id DESC LIMIT ' . (int)$limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lot_id' => $lotId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
