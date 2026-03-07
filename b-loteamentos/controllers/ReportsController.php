<?php
declare(strict_types=1);

namespace Controllers;

use Core\Auth;
use Core\Controller;

require_once __DIR__ . '/../config/database.php';

final class ReportsController extends Controller
{
    public function lots(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $db = Database::getInstance();

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        $quadra = isset($_GET['quadra']) ? trim((string)$_GET['quadra']) : '';

        $allowedStatus = ['','disponivel','reservado','vendido','indisponivel'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = '';
        }

        $projects = $db->query('SELECT id, name FROM projects ORDER BY id DESC')->fetchAll();
        if (!is_array($projects)) {
            $projects = [];
        }

        $where = [];
        $paramsSql = [];

        if ($projectId > 0) {
            $where[] = 'l.project_id = :project_id';
            $paramsSql['project_id'] = $projectId;
        }

        if ($status !== '') {
            $where[] = 'l.status = :status';
            $paramsSql['status'] = $status;
        }

        if ($quadra !== '') {
            $where[] = 'l.quadra LIKE :quadra';
            $paramsSql['quadra'] = '%' . $quadra . '%';
        }

        $sql = 'SELECT l.*, p.name AS project_name FROM lots l INNER JOIN projects p ON p.id = l.project_id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY l.project_id DESC, l.id DESC LIMIT 2000';

        $stmt = $db->prepare($sql);
        $stmt->execute($paramsSql);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            $rows = [];
        }

        $this->view('reports/lots', [
            'projects' => $projects,
            'rows' => $rows,
            'filters' => [
                'project_id' => $projectId,
                'status' => $status,
                'quadra' => $quadra,
            ],
        ]);
    }

    public function lotsCsv(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $db = Database::getInstance();

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        $quadra = isset($_GET['quadra']) ? trim((string)$_GET['quadra']) : '';

        $allowedStatus = ['','disponivel','reservado','vendido','indisponivel'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = '';
        }

        $where = [];
        $paramsSql = [];

        if ($projectId > 0) {
            $where[] = 'l.project_id = :project_id';
            $paramsSql['project_id'] = $projectId;
        }

        if ($status !== '') {
            $where[] = 'l.status = :status';
            $paramsSql['status'] = $status;
        }

        if ($quadra !== '') {
            $where[] = 'l.quadra LIKE :quadra';
            $paramsSql['quadra'] = '%' . $quadra . '%';
        }

        $sql = 'SELECT p.name AS project_name, l.polygon_id, l.numero_lote, l.quadra, l.area_m2, l.frente_m, l.fundo_m, l.lateral_esq_m, l.lateral_dir_m, l.valor, l.status, l.observacoes, l.created_at, l.updated_at FROM lots l INNER JOIN projects p ON p.id = l.project_id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY l.project_id DESC, l.id DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($paramsSql);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            $rows = [];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="lotes.csv"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            exit;
        }

        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Projeto','Polygon ID','Número Lote','Quadra','Área m²','Frente m','Fundo m','Lateral Esq m','Lateral Dir m','Valor','Status','Observações','Criado em','Atualizado em'], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                (string)($r['project_name'] ?? ''),
                (string)($r['polygon_id'] ?? ''),
                (string)($r['numero_lote'] ?? ''),
                (string)($r['quadra'] ?? ''),
                (string)($r['area_m2'] ?? ''),
                (string)($r['frente_m'] ?? ''),
                (string)($r['fundo_m'] ?? ''),
                (string)($r['lateral_esq_m'] ?? ''),
                (string)($r['lateral_dir_m'] ?? ''),
                (string)($r['valor'] ?? ''),
                (string)($r['status'] ?? ''),
                (string)($r['observacoes'] ?? ''),
                (string)($r['created_at'] ?? ''),
                (string)($r['updated_at'] ?? ''),
            ], ';');
        }

        fclose($out);
        exit;
    }

    public function reservations(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $db = Database::getInstance();

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        $from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

        $allowedStatus = ['','active','expired','converted','cancelled'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = '';
        }

        if ($from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = '';
        }
        if ($to !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = '';
        }

        $projects = $db->query('SELECT id, name FROM projects ORDER BY id DESC')->fetchAll();
        if (!is_array($projects)) {
            $projects = [];
        }

        $where = [];
        $paramsSql = [];

        if ($projectId > 0) {
            $where[] = 'l.project_id = :project_id';
            $paramsSql['project_id'] = $projectId;
        }

        if ($status !== '') {
            $where[] = 'r.status = :status';
            $paramsSql['status'] = $status;
        }

        if ($from !== '') {
            $where[] = 'DATE(r.created_at) >= :from';
            $paramsSql['from'] = $from;
        }

        if ($to !== '') {
            $where[] = 'DATE(r.created_at) <= :to';
            $paramsSql['to'] = $to;
        }

        if ((string)($_SESSION['user_role'] ?? '') === 'corretor') {
            $where[] = 'r.corretor_id = :corretor_id';
            $paramsSql['corretor_id'] = (int)($_SESSION['user_id'] ?? 0);
        }

        $sql = "SELECT r.*, l.polygon_id, l.numero_lote, l.quadra, l.status AS lot_status, p.name AS project_name, u.name AS corretor_name
                FROM reservations r
                INNER JOIN lots l ON l.id = r.lot_id
                INNER JOIN projects p ON p.id = l.project_id
                INNER JOIN users u ON u.id = r.corretor_id";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY r.id DESC LIMIT 2000';

        $stmt = $db->prepare($sql);
        $stmt->execute($paramsSql);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            $rows = [];
        }

        $this->view('reports/reservations', [
            'projects' => $projects,
            'rows' => $rows,
            'filters' => [
                'project_id' => $projectId,
                'status' => $status,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function reservationsCsv(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $db = Database::getInstance();

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        $from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

        $allowedStatus = ['','active','expired','converted','cancelled'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = '';
        }

        if ($from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = '';
        }
        if ($to !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = '';
        }

        $where = [];
        $paramsSql = [];

        if ($projectId > 0) {
            $where[] = 'l.project_id = :project_id';
            $paramsSql['project_id'] = $projectId;
        }

        if ($status !== '') {
            $where[] = 'r.status = :status';
            $paramsSql['status'] = $status;
        }

        if ($from !== '') {
            $where[] = 'DATE(r.created_at) >= :from';
            $paramsSql['from'] = $from;
        }

        if ($to !== '') {
            $where[] = 'DATE(r.created_at) <= :to';
            $paramsSql['to'] = $to;
        }

        if ((string)($_SESSION['user_role'] ?? '') === 'corretor') {
            $where[] = 'r.corretor_id = :corretor_id';
            $paramsSql['corretor_id'] = (int)($_SESSION['user_id'] ?? 0);
        }

        $sql = "SELECT p.name AS project_name, l.polygon_id, l.numero_lote, l.quadra, r.status, r.buyer_name, r.buyer_phone, r.buyer_email, r.expires_at, r.created_at, u.name AS corretor_name, r.notes
                FROM reservations r
                INNER JOIN lots l ON l.id = r.lot_id
                INNER JOIN projects p ON p.id = l.project_id
                INNER JOIN users u ON u.id = r.corretor_id";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY r.id DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($paramsSql);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            $rows = [];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reservas.csv"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            exit;
        }

        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Projeto','Polygon ID','Número Lote','Quadra','Status Reserva','Cliente','Telefone','Email','Expira em','Criado em','Corretor','Observações'], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                (string)($r['project_name'] ?? ''),
                (string)($r['polygon_id'] ?? ''),
                (string)($r['numero_lote'] ?? ''),
                (string)($r['quadra'] ?? ''),
                (string)($r['status'] ?? ''),
                (string)($r['buyer_name'] ?? ''),
                (string)($r['buyer_phone'] ?? ''),
                (string)($r['buyer_email'] ?? ''),
                (string)($r['expires_at'] ?? ''),
                (string)($r['created_at'] ?? ''),
                (string)($r['corretor_name'] ?? ''),
                (string)($r['notes'] ?? ''),
            ], ';');
        }

        fclose($out);
        exit;
    }

    public function sales(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $db = Database::getInstance();

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        $from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

        if ($from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = '';
        }
        if ($to !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = '';
        }

        $projects = $db->query('SELECT id, name FROM projects ORDER BY id DESC')->fetchAll();
        if (!is_array($projects)) {
            $projects = [];
        }

        $where = [];
        $paramsSql = [];

        if ($projectId > 0) {
            $where[] = 'l.project_id = :project_id';
            $paramsSql['project_id'] = $projectId;
        }

        if ($from !== '') {
            $where[] = 's.sale_date >= :from';
            $paramsSql['from'] = $from;
        }

        if ($to !== '') {
            $where[] = 's.sale_date <= :to';
            $paramsSql['to'] = $to;
        }

        if ((string)($_SESSION['user_role'] ?? '') === 'corretor') {
            $where[] = 's.corretor_id = :corretor_id';
            $paramsSql['corretor_id'] = (int)($_SESSION['user_id'] ?? 0);
        }

        $sql = "SELECT s.*, p.name AS project_name, l.polygon_id, l.numero_lote, l.quadra, u.name AS corretor_name
                FROM sales s
                INNER JOIN lots l ON l.id = s.lot_id
                INNER JOIN projects p ON p.id = l.project_id
                INNER JOIN users u ON u.id = s.corretor_id";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY s.id DESC LIMIT 2000';

        $stmt = $db->prepare($sql);
        $stmt->execute($paramsSql);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            $rows = [];
        }

        $totalValue = 0.0;
        foreach ($rows as $r) {
            $totalValue += (float)($r['final_value'] ?? 0);
        }

        $this->view('reports/sales', [
            'projects' => $projects,
            'rows' => $rows,
            'totalValue' => $totalValue,
            'filters' => [
                'project_id' => $projectId,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function salesCsv(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $db = Database::getInstance();

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        $from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

        if ($from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = '';
        }
        if ($to !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = '';
        }

        $where = [];
        $paramsSql = [];

        if ($projectId > 0) {
            $where[] = 'l.project_id = :project_id';
            $paramsSql['project_id'] = $projectId;
        }

        if ($from !== '') {
            $where[] = 's.sale_date >= :from';
            $paramsSql['from'] = $from;
        }

        if ($to !== '') {
            $where[] = 's.sale_date <= :to';
            $paramsSql['to'] = $to;
        }

        if ((string)($_SESSION['user_role'] ?? '') === 'corretor') {
            $where[] = 's.corretor_id = :corretor_id';
            $paramsSql['corretor_id'] = (int)($_SESSION['user_id'] ?? 0);
        }

        $sql = "SELECT p.name AS project_name, l.polygon_id, l.numero_lote, l.quadra, s.buyer_name, s.buyer_document, s.buyer_phone, s.buyer_email, s.sale_date, s.final_value, s.payment_method, u.name AS corretor_name, s.notes, s.created_at
                FROM sales s
                INNER JOIN lots l ON l.id = s.lot_id
                INNER JOIN projects p ON p.id = l.project_id
                INNER JOIN users u ON u.id = s.corretor_id";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY s.id DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($paramsSql);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            $rows = [];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vendas.csv"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            exit;
        }

        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Projeto','Polygon ID','Número Lote','Quadra','Cliente','CPF/CNPJ','Telefone','Email','Data Venda','Valor Final','Forma Pagamento','Corretor','Observações','Criado em'], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                (string)($r['project_name'] ?? ''),
                (string)($r['polygon_id'] ?? ''),
                (string)($r['numero_lote'] ?? ''),
                (string)($r['quadra'] ?? ''),
                (string)($r['buyer_name'] ?? ''),
                (string)($r['buyer_document'] ?? ''),
                (string)($r['buyer_phone'] ?? ''),
                (string)($r['buyer_email'] ?? ''),
                (string)($r['sale_date'] ?? ''),
                (string)($r['final_value'] ?? ''),
                (string)($r['payment_method'] ?? ''),
                (string)($r['corretor_name'] ?? ''),
                (string)($r['notes'] ?? ''),
                (string)($r['created_at'] ?? ''),
            ], ';');
        }

        fclose($out);
        exit;
    }
}
