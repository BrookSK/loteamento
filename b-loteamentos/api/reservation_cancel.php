<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Model.php';

require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/ReservationModel.php';

use Core\Auth;
use Models\HistoryModel;
use Models\ReservationModel;

Auth::init();
Auth::requireRole(['admin','profissional','corretor']);

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
    exit;
}

$csrfHeader = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);
if (!is_array($data)) {
    $data = $_POST;
}

$csrf = $csrfHeader !== '' ? $csrfHeader : (isset($data['csrf']) ? (string)$data['csrf'] : '');
if (!Auth::verifyCsrf($csrf)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'error' => 'CSRF inválido']);
    exit;
}

$reservationId = isset($data['reservation_id']) ? (int)$data['reservation_id'] : 0;
if ($reservationId <= 0) {
    echo json_encode(['success' => false, 'error' => 'reservation_id inválido']);
    exit;
}

$db = Database::getInstance();
$resModel = new ReservationModel();
$history = new HistoryModel();

try {
    $db->beginTransaction();

    $res = $resModel->getById($reservationId);
    if ($res === false) {
        throw new RuntimeException('Reserva não encontrada');
    }

    if ((string)$res['status'] !== 'active') {
        throw new RuntimeException('Reserva não está ativa');
    }

    $userRole = (string)($_SESSION['user_role'] ?? '');
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    if ($userRole === 'corretor' && (int)$res['corretor_id'] !== $userId) {
        throw new RuntimeException('Sem permissão para cancelar');
    }

    $lotId = (int)$res['lot_id'];

    $resModel->setStatus($reservationId, 'cancelled');

    $stmt = $db->prepare("UPDATE lots SET status = 'disponivel' WHERE id = :id");
    $stmt->execute(['id' => $lotId]);

    $history->add($lotId, $userId, 'reservation_cancelled', [
        'status' => 'reservado',
        'reservation_id' => $reservationId,
    ], [
        'status' => 'disponivel',
    ]);

    $db->commit();

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
