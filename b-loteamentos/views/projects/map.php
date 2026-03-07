<?php
$projectId = (int)($project['id'] ?? 0);
$svgRaw = (string)($project['svg_raw'] ?? '');
?>

<h1 class="page-title">Mapa: <?php echo htmlspecialchars((string)($project['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>

<div class="card">
    <div class="muted">Clique em um lote para ver detalhes e executar ações.</div>

    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <button class="btn" type="button" style="width:auto; padding:10px 14px; background:#475569;" data-filter="all">Todos</button>
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" data-filter="disponivel">Disponível</button>
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" data-filter="reservado">Reservado</button>
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" data-filter="vendido">Vendido</button>
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" data-filter="indisponivel">Indisponível</button>
        <div class="muted" id="map-msg" style="font-size:12px;"></div>
    </div>

    <div style="margin-top:12px; display:grid; grid-template-columns: 1fr 360px; gap:12px; align-items:start;">
        <div id="map-container" style="border:1px solid rgba(15,23,42,0.10); border-radius:12px; padding:12px; overflow:auto; max-height: 620px;">
            <?php if ($svgRaw !== ''): ?>
                <?php echo $svgRaw; ?>
            <?php else: ?>
                <div class="alert">SVG não disponível.</div>
            <?php endif; ?>
        </div>

        <div class="card" style="padding:14px;" id="lot-panel">
            <div class="muted" style="font-size:12px;">Lote selecionado</div>
            <div class="strong" id="lot-title">Nenhum</div>
            <div class="muted" id="lot-subtitle" style="margin-top:6px; font-size:12px;"></div>

            <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="button" style="width:auto; padding:10px 14px;" id="btn-reserve">Reservar</button>
                <button class="btn" type="button" style="width:auto; padding:10px 14px; background:#475569;" id="btn-cancel-reservation">Cancelar Reserva</button>
                <button class="btn" type="button" style="width:auto; padding:10px 14px;" id="btn-sell">Registrar Venda</button>
            </div>

            <div style="margin-top:12px;" id="lot-details" class="muted"></div>
        </div>
    </div>

    <div style="margin-top:12px;">
        <a class="link" href="<?php echo htmlspecialchars(APP_URL . '/projects', ENT_QUOTES, 'UTF-8'); ?>">Voltar</a>
    </div>
</div>

<script>
window.__PROJECT_ID__ = <?php echo (int)$projectId; ?>;
window.__APP_URL__ = <?php echo json_encode(APP_URL, JSON_UNESCAPED_SLASHES); ?>;
window.__CSRF__ = <?php echo json_encode((string)($_SESSION[CSRF_KEY] ?? ''), JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="<?php echo htmlspecialchars(APP_URL . '/assets/js/map.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
