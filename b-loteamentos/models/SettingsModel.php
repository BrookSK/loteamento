<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

final class SettingsModel extends Model
{
    public function get(string $key): string|null
    {
        $sql = 'SELECT `value` FROM settings WHERE `key` = :key LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        if ($value === false) {
            return null;
        }
        return $value === null ? null : (string)$value;
    }

    public function getMany(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $in = [];
        $params = [];
        foreach (array_values($keys) as $i => $key) {
            $p = ':k' . $i;
            $in[] = $p;
            $params[$p] = $key;
        }

        $sql = 'SELECT `key`, `value` FROM settings WHERE `key` IN (' . implode(',', $in) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $out = [];
        foreach ($rows as $row) {
            if (!isset($row['key'])) {
                continue;
            }
            $out[(string)$row['key']] = array_key_exists('value', $row) ? ($row['value'] === null ? null : (string)$row['value']) : null;
        }
        return $out;
    }

    public function set(string $key, ?string $value): void
    {
        $sql = 'INSERT INTO settings (`key`, `value`) VALUES (:key, :value) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'key' => $key,
            'value' => $value,
        ]);
    }
}
