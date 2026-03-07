<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Model.php';

require_once __DIR__ . '/../models/LotModel.php';
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/ReservationModel.php';
require_once __DIR__ . '/../models/SettingsModel.php';

use Core\Auth;
use Models\LotModel;
use Models\HistoryModel;
use Models\ReservationModel;
use Models\SettingsModel;

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

$lotId = isset($data['lot_id']) ? (int)$data['lot_id'] : 0;
$buyerName = isset($data['buyer_name']) ? trim((string)$data['buyer_name']) : '';
$buyerPhone = isset($data['buyer_phone']) ? trim((string)$data['buyer_phone']) : '';
$buyerEmail = isset($data['buyer_email']) ? trim((string)$data['buyer_email']) : '';
$notes = isset($data['notes']) ? trim((string)$data['notes']) : '';

if ($lotId <= 0 || $buyerName === '' || $buyerPhone === '') {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

$db = Database::getInstance();
$lotModel = new LotModel();
$resModel = new ReservationModel();
$history = new HistoryModel();

try {
    $db->beginTransaction();

    $lot = $db->prepare('SELECT * FROM lots WHERE id = :id LIMIT 1');
    $lot->execute(['id' => $lotId]);
    $lotRow = $lot->fetch();

    if ($lotRow === false) {
        throw new RuntimeException('Lote não encontrado');
    }

    if ((string)$lotRow['status'] !== 'disponivel') {
        throw new RuntimeException('Lote não disponível');
    }

    $existing = $resModel->getActiveByLot($lotId);
    if ($existing !== false) {
        throw new RuntimeException('Já existe reserva ativa');
    }

    $settings = new SettingsModel();
    $hoursStr = $settings->get('reservation_hours');
    $hours = ($hoursStr !== null && ctype_digit($hoursStr) && (int)$hoursStr > 0) ? (int)$hoursStr : RESERVATION_HOURS;

    $expiresAt = (new DateTimeImmutable('now'))->modify('+' . $hours . ' hours')->format('Y-m-d H:i:s');

    $corretorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    $reservationId = $resModel->create([
        'lot_id' => $lotId,
        'corretor_id' => $corretorId,
        'buyer_name' => $buyerName,
        'buyer_phone' => $buyerPhone,
        'buyer_email' => $buyerEmail !== '' ? $buyerEmail : null,
        'expires_at' => $expiresAt,
        'status' => 'active',
        'notes' => $notes !== '' ? $notes : null,
    ]);

    $stmt = $db->prepare("UPDATE lots SET status = 'reservado' WHERE id = :id");
    $stmt->execute(['id' => $lotId]);

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $history->add($lotId, $userId, 'reservation', [
        'status' => 'disponivel',
    ], [
        'status' => 'reservado',
        'reservation_id' => $reservationId,
        'expires_at' => $expiresAt,
        'buyer_name' => $buyerName,
        'buyer_phone' => $buyerPhone,
        'buyer_email' => $buyerEmail !== '' ? $buyerEmail : null,
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'reservation_id' => $reservationId,
        'expires_at' => $expiresAt,
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
