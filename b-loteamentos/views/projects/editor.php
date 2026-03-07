<?php
$projectId = (int)($project['id'] ?? 0);
$svgRaw = (string)($project['svg_raw'] ?? '');
?>

<h1 class="page-title">Editor: <?php echo htmlspecialchars((string)($project['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>

<div class="card">
    <div class="muted">Associe cada polígono do SVG a um lote.</div>

    <div style="margin-top:12px; display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" id="btn-zoom-in">Zoom +</button>
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" id="btn-zoom-out">Zoom -</button>
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" id="btn-reset">Reset</button>
        <button class="btn" type="button" style="width:auto; padding:10px 14px;" id="btn-toggle-ids">Mostrar IDs</button>

        <form id="csv-form" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;" enctype="multipart/form-data">
            <input type="file" name="csv" accept="text/csv" required>
            <button class="btn" type="submit" style="width:auto; padding:10px 14px;">Importar CSV</button>
        </form>

        <div class="badge" id="progress">0 associados</div>
    </div>

    <div style="margin-top:12px; display:grid; grid-template-columns: 1fr 360px; gap:12px; align-items:start;">
        <div style="border:1px solid rgba(15,23,42,0.10); border-radius:12px; padding:12px; overflow:auto; max-height: 640px;" id="svg-wrap">
            <?php if ($svgRaw !== ''): ?>
                <div id="svg-root"><?php echo $svgRaw; ?></div>
            <?php else: ?>
                <div class="alert">SVG não disponível. Verifique as chaves em <a class="link" href="<?php echo htmlspecialchars(APP_URL . '/settings', ENT_QUOTES, 'UTF-8'); ?>">Configurações</a>.</div>
            <?php endif; ?>
        </div>

        <div class="card" style="padding:14px;" id="side-panel">
            <div class="muted" style="font-size:12px;">Polígono selecionado</div>
            <div class="strong" id="selected-polygon">Nenhum</div>

            <form class="form" id="lot-form" style="margin-top:12px;">
                <div class="field">
                    <label for="numero_lote">Número do lote</label>
                    <input id="numero_lote" name="numero_lote" type="text">
                </div>
                <div class="field">
                    <label for="quadra">Quadra</label>
                    <input id="quadra" name="quadra" type="text">
                </div>
                <div class="field">
                    <label for="area_m2">Área m²</label>
                    <input id="area_m2" name="area_m2" type="text">
                </div>
                <div class="field">
                    <label for="frente_m">Frente (m)</label>
                    <input id="frente_m" name="frente_m" type="text">
                </div>
                <div class="field">
                    <label for="fundo_m">Fundo (m)</label>
                    <input id="fundo_m" name="fundo_m" type="text">
                </div>
                <div class="field">
                    <label for="lateral_esq_m">Lateral esq. (m)</label>
                    <input id="lateral_esq_m" name="lateral_esq_m" type="text">
                </div>
                <div class="field">
                    <label for="lateral_dir_m">Lateral dir. (m)</label>
                    <input id="lateral_dir_m" name="lateral_dir_m" type="text">
                </div>
                <div class="field">
                    <label for="valor">Valor (R$)</label>
                    <input id="valor" name="valor" type="text">
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" style="width:100%; padding:11px 12px; border-radius:10px; border:1px solid rgba(15,23,42,0.14);">
                        <option value="disponivel">disponivel</option>
                        <option value="reservado">reservado</option>
                        <option value="vendido">vendido</option>
                        <option value="indisponivel">indisponivel</option>
                    </select>
                </div>
                <div class="field">
                    <label for="observacoes">Observações</label>
                    <textarea id="observacoes" name="observacoes" style="width:100%; padding:11px 12px; border-radius:10px; border:1px solid rgba(15,23,42,0.14); min-height: 90px;"></textarea>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <button class="btn" type="submit" style="width:auto; padding:10px 14px;" id="btn-save">Salvar</button>
                    <button class="btn" type="button" style="width:auto; padding:10px 14px; background:#475569;" id="btn-clear">Limpar</button>
                    <div class="muted" id="save-msg" style="font-size:12px;"></div>
                </div>
            </form>
        </div>
    </div>

    <div style="margin-top:12px;">
        <a class="link" href="<?php echo htmlspecialchars(APP_URL . '/projects', ENT_QUOTES, 'UTF-8'); ?>">Voltar</a>
        <a class="link" style="margin-left:10px;" href="<?php echo htmlspecialchars(APP_URL . '/projects/' . $projectId . '/map', ENT_QUOTES, 'UTF-8'); ?>">Abrir mapa</a>
    </div>
</div>

<script>
window.__PROJECT_ID__ = <?php echo (int)$projectId; ?>;
window.__APP_URL__ = <?php echo json_encode(APP_URL, JSON_UNESCAPED_SLASHES); ?>;
window.__CSRF__ = <?php echo json_encode((string)($_SESSION[CSRF_KEY] ?? ''), JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="<?php echo htmlspecialchars(APP_URL . '/assets/js/editor.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
