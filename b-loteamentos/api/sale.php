<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Validator.php';

require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/ReservationModel.php';
require_once __DIR__ . '/../models/SaleModel.php';

use Core\Auth;
use Core\Validator;
use Models\HistoryModel;
use Models\ReservationModel;
use Models\SaleModel;

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
$reservationId = isset($data['reservation_id']) ? (int)$data['reservation_id'] : null;
$buyerName = isset($data['buyer_name']) ? trim((string)$data['buyer_name']) : '';
$buyerDocument = isset($data['buyer_document']) ? trim((string)$data['buyer_document']) : '';
$buyerPhone = isset($data['buyer_phone']) ? trim((string)$data['buyer_phone']) : '';
$buyerEmail = isset($data['buyer_email']) ? trim((string)$data['buyer_email']) : '';
$saleDate = isset($data['sale_date']) ? trim((string)$data['sale_date']) : '';
$finalValue = isset($data['final_value']) ? (float)$data['final_value'] : 0.0;
$paymentMethod = isset($data['payment_method']) ? trim((string)$data['payment_method']) : '';
$notes = isset($data['notes']) ? trim((string)$data['notes']) : '';

if ($lotId <= 0 || $buyerName === '' || $buyerDocument === '' || $buyerPhone === '' || $saleDate === '' || $finalValue <= 0) {
    echo json_encode(['success' => false, 'error' => 'Dados obrigatórios inválidos']);
    exit;
}

if (!Validator::cpfCnpj($buyerDocument)) {
    echo json_encode(['success' => false, 'error' => 'CPF/CNPJ inválido']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $saleDate)) {
    echo json_encode(['success' => false, 'error' => 'Data inválida']);
    exit;
}

$db = Database::getInstance();
$saleModel = new SaleModel();
$resModel = new ReservationModel();
$history = new HistoryModel();

try {
    $db->beginTransaction();

    $lotStmt = $db->prepare('SELECT * FROM lots WHERE id = :id LIMIT 1');
    $lotStmt->execute(['id' => $lotId]);
    $lot = $lotStmt->fetch();

    if ($lot === false) {
        throw new RuntimeException('Lote não encontrado');
    }

    $lotStatus = (string)$lot['status'];
    if ($lotStatus !== 'disponivel' && $lotStatus !== 'reservado') {
        throw new RuntimeException('Lote não permite venda');
    }

    $activeReservation = $resModel->getActiveByLot($lotId);
    $useReservationId = null;

    if ($activeReservation !== false) {
        $useReservationId = (int)$activeReservation['id'];
    }

    if (is_int($reservationId) && $reservationId > 0) {
        $useReservationId = $reservationId;
    }

    $corretorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    $saleId = $saleModel->create([
        'lot_id' => $lotId,
        'reservation_id' => $useReservationId,
        'corretor_id' => $corretorId,
        'buyer_name' => $buyerName,
        'buyer_document' => $buyerDocument,
        'buyer_phone' => $buyerPhone,
        'buyer_email' => $buyerEmail !== '' ? $buyerEmail : null,
        'sale_date' => $saleDate,
        'final_value' => $finalValue,
        'payment_method' => $paymentMethod !== '' ? $paymentMethod : null,
        'notes' => $notes !== '' ? $notes : null,
    ]);

    if ($useReservationId !== null) {
        $resModel->setStatus($useReservationId, 'converted');
    }

    $upd = $db->prepare("UPDATE lots SET status = 'vendido' WHERE id = :id");
    $upd->execute(['id' => $lotId]);

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $history->add($lotId, $userId, 'sale', [
        'status' => $lotStatus,
    ], [
        'status' => 'vendido',
        'sale_id' => $saleId,
        'reservation_id' => $useReservationId,
        'buyer_name' => $buyerName,
        'buyer_document' => $buyerDocument,
        'buyer_phone' => $buyerPhone,
        'buyer_email' => $buyerEmail !== '' ? $buyerEmail : null,
        'sale_date' => $saleDate,
        'final_value' => $finalValue,
    ]);

    $db->commit();

    echo json_encode(['success' => true, 'sale_id' => $saleId], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
