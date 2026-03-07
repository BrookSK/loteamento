<?php
declare(strict_types=1);

$filters = isset($filters) && is_array($filters) ? $filters : [];
$projects = isset($projects) && is_array($projects) ? $projects : [];
$rows = isset($rows) && is_array($rows) ? $rows : [];
$totalValue = (float)($totalValue ?? 0);

$projectId = (int)($filters['project_id'] ?? 0);
$from = (string)($filters['from'] ?? '');
$to = (string)($filters['to'] ?? '');

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>

<h1 class="page-title">Relatório - Vendas</h1>

<div class="card">
    <form method="get" action="<?php echo h(APP_URL . '/reports/sales'); ?>" class="form" style="grid-template-columns: repeat(4, minmax(0, 1fr)); align-items:end;">
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
                $qs = http_build_query(['project_id' => $projectId, 'from' => $from, 'to' => $to]);
                echo h(APP_URL . '/reports/sales.csv' . ($qs !== '' ? ('?' . $qs) : ''));
            ?>">Exportar CSV</a>
        </div>
    </form>
</div>

<div class="card" style="margin-top:12px;">
    <div class="muted">Total do período</div>
    <div class="strong">R$ <?php echo number_format($totalValue, 2, ',', '.'); ?></div>
</div>

<div class="card" style="margin-top:12px; overflow:auto;">
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Projeto</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Lote</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Quadra</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Cliente</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">CPF/CNPJ</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Data</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Valor</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid var(--border);">Corretor</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr>
                    <td colspan="8" class="muted" style="padding:12px;">Nenhum registro.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($rows as $r): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['project_name'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['numero_lote'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['quadra'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['buyer_name'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['buyer_document'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['sale_date'] ?? '')); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);">R$ <?php echo number_format((float)($r['final_value'] ?? 0), 2, ',', '.'); ?></td>
                    <td style="padding:10px; border-bottom:1px solid var(--border);"><?php echo h((string)($r['corretor_name'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
