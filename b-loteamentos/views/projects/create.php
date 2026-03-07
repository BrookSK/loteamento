<h1 class="page-title">Novo projeto</h1>

<?php if (isset($error) && is_string($error) && $error !== ''): ?>
    <div class="alert">Erro: <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="card" style="max-width: 820px;">
    <form class="form" method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars(APP_URL . '/projects/store', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="field">
            <label for="name">Nome do loteamento</label>
            <input id="name" name="name" type="text" required>
        </div>

        <div class="field">
            <label for="location">Localização</label>
            <input id="location" name="location" type="text">
        </div>

        <div class="field">
            <label for="description">Descrição</label>
            <textarea id="description" name="description" style="width:100%; padding:11px 12px; border-radius:10px; border:1px solid rgba(15,23,42,0.14); min-height: 110px;"></textarea>
        </div>

        <div class="field">
            <label for="cover_image">Imagem de capa (JPG/PNG/WebP, até 5MB)</label>
            <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" required>
        </div>

        <div class="field">
            <label for="original_image">Planta para vetorizar (JPG/PNG/WebP, até 20MB)</label>
            <input id="original_image" name="original_image" type="file" accept="image/jpeg,image/png,image/webp" required>
        </div>

        <button class="btn" type="submit" style="width:auto; padding:10px 14px;">Criar e vetorizar</button>
        <a class="link" style="margin-left:10px;" href="<?php echo htmlspecialchars(APP_URL . '/projects', ENT_QUOTES, 'UTF-8'); ?>">Voltar</a>
    </form>
</div>
