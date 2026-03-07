<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../core/Model.php';

require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/ReservationModel.php';

use Models\HistoryModel;
use Models\ReservationModel;

header('Content-Type: text/plain; charset=utf-8');

$db = Database::getInstance();
$resModel = new ReservationModel();
$history = new HistoryModel();

$now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

try {
    $db->beginTransaction();

    $stmt = $db->prepare("SELECT r.*, l.status AS lot_status FROM reservations r INNER JOIN lots l ON l.id = r.lot_id WHERE r.status = 'active' AND r.expires_at < :now FOR UPDATE");
    $stmt->execute(['now' => $now]);
    $rows = $stmt->fetchAll();
    if (!is_array($rows) || $rows === []) {
        $db->commit();
        echo "OK: 0 expiradas\n";
        exit;
    }

    $expiredCount = 0;

    foreach ($rows as $r) {
        $reservationId = (int)($r['id'] ?? 0);
        $lotId = (int)($r['lot_id'] ?? 0);
        $lotStatus = (string)($r['lot_status'] ?? '');

        if ($reservationId <= 0 || $lotId <= 0) {
            continue;
        }

        $resModel->setStatus($reservationId, 'expired');

        if ($lotStatus === 'reservado') {
            $upd = $db->prepare("UPDATE lots SET status = 'disponivel' WHERE id = :id");
            $upd->execute(['id' => $lotId]);

            $history->add($lotId, null, 'reservation_expired', [
                'status' => 'reservado',
                'reservation_id' => $reservationId,
            ], [
                'status' => 'disponivel',
            ]);
        } else {
            $history->add($lotId, null, 'reservation_expired', [
                'status' => $lotStatus,
                'reservation_id' => $reservationId,
            ], [
                'status' => $lotStatus,
            ]);
        }

        $expiredCount++;
    }

    $db->commit();

    echo "OK: {$expiredCount} expiradas\n";
    exit;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
