<div class="row" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <h1 class="page-title" style="margin:0;">Configurações</h1>
</div>

<?php if (isset($flash) && $flash !== ''): ?>
    <div class="card" style="margin-top:12px; border-color: rgba(37,99,235,0.22);">
        <div class="muted">Ação realizada: <?php echo htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php endif; ?>

<div class="card" style="margin-top:12px; max-width: 720px;">
    <form class="form" method="post" action="<?php echo htmlspecialchars(APP_URL . '/settings/update', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="field">
            <label for="vectorizer_api_id">Vectorizer API ID</label>
            <input id="vectorizer_api_id" name="vectorizer_api_id" type="text" value="<?php echo htmlspecialchars((string)($settings['vectorizer_api_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="field">
            <label for="vectorizer_api_secret">Vectorizer API Secret</label>
            <input id="vectorizer_api_secret" name="vectorizer_api_secret" type="password" value="<?php echo htmlspecialchars((string)($settings['vectorizer_api_secret'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="muted" style="font-size:12px; margin-top:6px;">Fica armazenado no banco. Não exibir em telas públicas.</div>
        </div>

        <div class="field">
            <label for="reservation_hours">Horas padrão de expiração de reserva</label>
            <input id="reservation_hours" name="reservation_hours" type="text" value="<?php echo htmlspecialchars((string)($settings['reservation_hours'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <button class="btn" type="submit" style="width:auto; padding:10px 14px;">Salvar</button>
    </form>
</div>
