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
Auth::requireRole(['admin','profissional']);

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

$projectId = isset($data['project_id']) ? (int)$data['project_id'] : 0;
$polygonId = isset($data['polygon_id']) ? trim((string)$data['polygon_id']) : '';

if ($projectId <= 0 || $polygonId === '') {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

$payload = [
    'numero_lote' => isset($data['numero_lote']) ? trim((string)$data['numero_lote']) : null,
    'quadra' => isset($data['quadra']) ? trim((string)$data['quadra']) : null,
    'area_m2' => isset($data['area_m2']) && $data['area_m2'] !== '' ? (float)$data['area_m2'] : null,
    'frente_m' => isset($data['frente_m']) && $data['frente_m'] !== '' ? (float)$data['frente_m'] : null,
    'fundo_m' => isset($data['fundo_m']) && $data['fundo_m'] !== '' ? (float)$data['fundo_m'] : null,
    'lateral_esq_m' => isset($data['lateral_esq_m']) && $data['lateral_esq_m'] !== '' ? (float)$data['lateral_esq_m'] : null,
    'lateral_dir_m' => isset($data['lateral_dir_m']) && $data['lateral_dir_m'] !== '' ? (float)$data['lateral_dir_m'] : null,
    'valor' => isset($data['valor']) && $data['valor'] !== '' ? (float)$data['valor'] : null,
    'status' => isset($data['status']) ? (string)$data['status'] : 'disponivel',
    'observacoes' => isset($data['observacoes']) ? (string)$data['observacoes'] : null,
];

$allowedStatus = ['disponivel', 'reservado', 'vendido', 'indisponivel'];
if (!in_array($payload['status'], $allowedStatus, true)) {
    $payload['status'] = 'disponivel';
}

$db = Database::getInstance();
$lotModel = new LotModel();
$historyModel = new HistoryModel();

try {
    $db->beginTransaction();

    $existing = $lotModel->getByProjectAndPolygon($projectId, $polygonId);

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    if ($existing === false) {
        $lotId = $lotModel->create($projectId, $polygonId, $payload);
        $historyModel->add($lotId, $userId, 'edit', null, [
            'project_id' => $projectId,
            'polygon_id' => $polygonId,
            ...$payload,
        ]);
    } else {
        $lotId = (int)$existing['id'];
        $old = $existing;
        unset($old['id'], $old['created_at'], $old['updated_at']);

        $lotModel->update($lotId, $payload);
        $historyModel->add($lotId, $userId, 'edit', $old, [
            'project_id' => $projectId,
            'polygon_id' => $polygonId,
            ...$payload,
        ]);
    }

    $db->commit();

    $lot = $lotModel->getByProjectAndPolygon($projectId, $polygonId);

    echo json_encode(['success' => true, 'lot' => $lot], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo json_encode(['success' => false, 'error' => 'Erro ao salvar lote'], JSON_UNESCAPED_UNICODE);
    exit;
}
