<?php
declare(strict_types=1);

$filters = isset($filters) && is_array($filters) ? $filters : [];
$projects = isset($projects) && is_array($projects) ? $projects : [];
$rows = isset($rows) && is_array($rows) ? $rows : [];

$projectId = (int)($filters['project_id'] ?? 0);
$status = (string)($filters['status'] ?? '');
$from = (string)($filters['from'] ?? '');
$to = (string)($filters['to'] ?? '');

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>

<h1 class="page-title">Relatório - Reservas</h1>

<div class="card">
    <form method="get" action="<?php echo h(APP_URL . '/reports/reservations'); ?>" class="form" style="grid-template-columns: repeat(5, minmax(0, 1fr)); align-items:end;">
        <div class="field">
            <label>Projeto</label>
            <select name="project_id" style="width:100%; padding:11px 12px; border-radius:10px; border: 1px solid rgba(15, 23, 42, 0.14);">
                <option value="0">Todos</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?php echo (int)$p['id']; ?>" <?php echo ((int)$p['id'] === $projectId) ? 'selected' : ''; ?>><?php echo h((string)$p['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Status</label>
            <select name="status" style="width:100%; padding:11px 12px; border-radius:10px; border: 1px solid rgba(15, 23, 42, 0.14);">
                <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>Todos</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Ativa</option>
                <option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Expirada</option>
                <option value="converted" <?php echo $status === 'converted' ? 'selected' : ''; ?>>Convertida</option>
                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelada</option>
            </select>
        </div>

        <div class="field">
            <label>De</label>
            <input type="date" name="from" value="<?php echo h($from); ?>" />
        </div>

        <div class="field">
            <label>Até</label>
            <input type="date" name="to" value="<?php echo h($to); ?>" />
        </div>

        <div style="display:flex; gap:10px;">
            <button class="btn" type="submit" style="width:auto; padding:11px 14px;">Filtrar</button>
            <a class="btn" style="width:auto; padding:11px 14px; background:#475569;" href="<?php
                $qs = http_build_query(['project_id' => $projectId, 'status' => $status, 'from' => $from, 'to' => $to]);
                echo h(APP_URL . '/reports/reservations.csv' . ($qs !== '' ? ('?' . $qs) : ''));
            ?>">Exportar CSV</a>
        </div>
    </form>
</div>

<div class="card" style="margin-top:12px; overflow:auto;">
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Projeto</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Lote</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Quadra</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Cliente</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Telefone</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Corretor</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Status</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Expira</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Criada</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr>
                    <td colspan="9" class="muted" style="padding:12px;">Nenhum registro.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($rows as $r): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['project_name'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['numero_lote'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['quadra'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['buyer_name'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['buyer_phone'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['corretor_name'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['status'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['expires_at'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['created_at'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
