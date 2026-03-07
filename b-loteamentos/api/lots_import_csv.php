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
Auth::requireRole(['admin']);

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método inválido']);
    exit;
}

$csrfHeader = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$csrf = $csrfHeader !== '' ? $csrfHeader : (isset($_POST['csrf']) ? (string)$_POST['csrf'] : '');
if (!Auth::verifyCsrf($csrf)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'error' => 'CSRF inválido']);
    exit;
}

$projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
if ($projectId <= 0) {
    echo json_encode(['success' => false, 'error' => 'project_id inválido']);
    exit;
}

if (!isset($_FILES['csv']) || !is_array($_FILES['csv']) || (int)($_FILES['csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'CSV não enviado']);
    exit;
}

$tmp = (string)($_FILES['csv']['tmp_name'] ?? '');
if ($tmp === '' || !is_uploaded_file($tmp)) {
    echo json_encode(['success' => false, 'error' => 'Arquivo inválido']);
    exit;
}

$fh = fopen($tmp, 'r');
if ($fh === false) {
    echo json_encode(['success' => false, 'error' => 'Não foi possível ler CSV']);
    exit;
}

$headers = fgetcsv($fh);
if (!is_array($headers)) {
    fclose($fh);
    echo json_encode(['success' => false, 'error' => 'CSV vazio']);
    exit;
}

$map = [];
foreach ($headers as $i => $h) {
    $key = strtolower(trim((string)$h));
    $map[$key] = $i;
}

$required = ['polygon_id'];
foreach ($required as $r) {
    if (!array_key_exists($r, $map)) {
        fclose($fh);
        echo json_encode(['success' => false, 'error' => 'CSV sem coluna obrigatória: ' . $r]);
        exit;
    }
}

$lotModel = new LotModel();
$historyModel = new HistoryModel();
$db = Database::getInstance();

$ok = 0;
$fail = 0;

try {
    $db->beginTransaction();

    while (($row = fgetcsv($fh)) !== false) {
        if (!is_array($row)) {
            continue;
        }

        $polygonId = trim((string)($row[$map['polygon_id']] ?? ''));
        if ($polygonId === '') {
            $fail++;
            continue;
        }

        $payload = [
            'numero_lote' => isset($map['numero_lote']) ? trim((string)($row[$map['numero_lote']] ?? '')) : null,
            'quadra' => isset($map['quadra']) ? trim((string)($row[$map['quadra']] ?? '')) : null,
            'area_m2' => isset($map['area_m2']) && ($row[$map['area_m2']] ?? '') !== '' ? (float)$row[$map['area_m2']] : null,
            'frente_m' => isset($map['frente_m']) && ($row[$map['frente_m']] ?? '') !== '' ? (float)$row[$map['frente_m']] : null,
            'fundo_m' => isset($map['fundo_m']) && ($row[$map['fundo_m']] ?? '') !== '' ? (float)$row[$map['fundo_m']] : null,
            'lateral_esq_m' => isset($map['lateral_esq_m']) && ($row[$map['lateral_esq_m']] ?? '') !== '' ? (float)$row[$map['lateral_esq_m']] : null,
            'lateral_dir_m' => isset($map['lateral_dir_m']) && ($row[$map['lateral_dir_m']] ?? '') !== '' ? (float)$row[$map['lateral_dir_m']] : null,
            'valor' => isset($map['valor']) && ($row[$map['valor']] ?? '') !== '' ? (float)$row[$map['valor']] : null,
            'status' => isset($map['status']) ? trim((string)($row[$map['status']] ?? 'disponivel')) : 'disponivel',
            'observacoes' => isset($map['observacoes']) ? (string)($row[$map['observacoes']] ?? '') : null,
        ];

        $allowedStatus = ['disponivel', 'reservado', 'vendido', 'indisponivel'];
        if (!in_array($payload['status'], $allowedStatus, true)) {
            $payload['status'] = 'disponivel';
        }

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

        $ok++;
    }

    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fclose($fh);
    echo json_encode(['success' => false, 'error' => 'Erro ao importar CSV']);
    exit;
}

fclose($fh);

echo json_encode(['success' => true, 'imported' => $ok, 'failed' => $fail], JSON_UNESCAPED_UNICODE);
exit;
