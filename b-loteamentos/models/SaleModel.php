<?php
declare(strict_types=1);

namespace Models;

use Core\Model;

final class SaleModel extends Model
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO sales (lot_id, reservation_id, corretor_id, buyer_name, buyer_document, buyer_phone, buyer_email, sale_date, final_value, payment_method, notes)
                VALUES (:lot_id, :reservation_id, :corretor_id, :buyer_name, :buyer_document, :buyer_phone, :buyer_email, :sale_date, :final_value, :payment_method, :notes)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'lot_id' => $data['lot_id'],
            'reservation_id' => $data['reservation_id'] ?? null,
            'corretor_id' => $data['corretor_id'],
            'buyer_name' => $data['buyer_name'],
            'buyer_document' => $data['buyer_document'] ?? null,
            'buyer_phone' => $data['buyer_phone'] ?? null,
            'buyer_email' => $data['buyer_email'] ?? null,
            'sale_date' => $data['sale_date'],
            'final_value' => $data['final_value'],
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }
}
