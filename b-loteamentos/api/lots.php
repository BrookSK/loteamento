<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Model.php';

require_once __DIR__ . '/../models/LotModel.php';
require_once __DIR__ . '/../models/HistoryModel.php';

use Core\Auth;
use Models\LotModel;
use Models\HistoryModel;

Auth::init();
Auth::requireRole(['admin','profissional','corretor']);

header('Content-Type: application/json; charset=utf-8');

$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
if ($projectId <= 0) {
    echo json_encode(['success' => false, 'error' => 'project_id inválido']);
    exit;
}

$historyModel = new HistoryModel();

$db = Database::getInstance();
$sql = "
    SELECT
        l.*,
        r.id AS reservation_id,
        r.buyer_name AS reservation_buyer_name,
        r.buyer_phone AS reservation_buyer_phone,
        r.buyer_email AS reservation_buyer_email,
        r.expires_at AS reservation_expires_at,
        u.name AS corretor_name
    FROM lots l
    LEFT JOIN reservations r
        ON r.lot_id = l.id
       AND r.status = 'active'
       AND r.expires_at >= NOW()
    LEFT JOIN users u
        ON u.id = r.corretor_id
    WHERE l.project_id = :project_id
    ORDER BY l.id DESC
";

$stmt = $db->prepare($sql);
$stmt->execute(['project_id' => $projectId]);
$lots = $stmt->fetchAll();

if (!is_array($lots)) {
    $lots = [];
}

foreach ($lots as &$lot) {
    $lotId = (int)($lot['id'] ?? 0);
    $lot['history'] = $lotId > 0 ? $historyModel->listByLot($lotId, 20) : [];

    $lot['buyer_name'] = $lot['reservation_buyer_name'] ?? null;
    $lot['reservation_expires'] = $lot['reservation_expires_at'] ?? null;
}
unset($lot);

echo json_encode(['success' => true, 'lots' => $lots], JSON_UNESCAPED_UNICODE);
exit;
