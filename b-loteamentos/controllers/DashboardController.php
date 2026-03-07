<?php
declare(strict_types=1);

namespace Controllers;

use Core\Auth;
use Core\Controller;

require_once __DIR__ . '/../config/database.php';

final class DashboardController extends Controller
{
    public function index(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $db = Database::getInstance();

        $projectsTotal = (int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn();
        $lotsTotal = (int)$db->query('SELECT COUNT(*) FROM lots')->fetchColumn();
        $soldLots = (int)$db->query("SELECT COUNT(*) FROM lots WHERE status = 'vendido'")->fetchColumn();

        $lotsByStatus = [
            'disponivel' => 0,
            'reservado' => 0,
            'vendido' => 0,
            'indisponivel' => 0,
        ];
        $stmt = $db->query('SELECT status, COUNT(*) AS c FROM lots GROUP BY status');
        $rows = $stmt->fetchAll();
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $s = isset($r['status']) ? (string)$r['status'] : '';
                if ($s === '' || !array_key_exists($s, $lotsByStatus)) {
                    continue;
                }
                $lotsByStatus[$s] = (int)($r['c'] ?? 0);
            }
        }

        $activeReservations = (int)$db->query("SELECT COUNT(*) FROM reservations WHERE status = 'active' AND expires_at >= NOW()")->fetchColumn();

        $todayExpired = (int)$db->query("SELECT COUNT(*) FROM reservations WHERE status = 'expired' AND DATE(expires_at) = CURDATE()")->fetchColumn();

        $monthStart = (new \DateTimeImmutable('first day of this month'))->format('Y-m-d');
        $salesStmt = $db->prepare('SELECT COUNT(*) AS cnt, COALESCE(SUM(final_value), 0) AS total FROM sales WHERE sale_date >= :start');
        $salesStmt->execute(['start' => $monthStart]);
        $salesRow = $salesStmt->fetch();
        $salesMonthCount = (int)($salesRow['cnt'] ?? 0);
        $salesMonthTotal = (float)($salesRow['total'] ?? 0);

        $this->view('dashboard/index', [
            'userName' => (string)($_SESSION['user_name'] ?? ''),
            'userRole' => (string)($_SESSION['user_role'] ?? ''),
            'projectsTotal' => $projectsTotal,
            'lotsTotal' => $lotsTotal,
            'soldLots' => $soldLots,
            'lotsByStatus' => $lotsByStatus,
            'activeReservations' => $activeReservations,
            'todayExpired' => $todayExpired,
            'salesMonthCount' => $salesMonthCount,
            'salesMonthTotal' => $salesMonthTotal,
        ]);
    }
}
