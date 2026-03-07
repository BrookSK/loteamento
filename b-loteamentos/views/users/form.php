<?php
$isEdit = is_array($user);
$idValue = $isEdit ? (int)$user['id'] : 0;
$nameValue = $isEdit ? (string)$user['name'] : '';
$emailValue = $isEdit ? (string)$user['email'] : '';
$roleValue = $isEdit ? (string)$user['role'] : 'corretor';
$activeValue = $isEdit ? (int)$user['active'] : 1;
?>

<h1 class="page-title"><?php echo $isEdit ? 'Editar usuário' : 'Novo usuário'; ?></h1>

<?php if (isset($error) && is_string($error) && $error !== ''): ?>
    <div class="alert">Erro: <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="card" style="max-width: 720px;">
    <form class="form" method="post" action="<?php echo htmlspecialchars(APP_URL . ($isEdit ? '/users/update' : '/users/store'), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$idValue, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>

        <div class="field">
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" required value="<?php echo htmlspecialchars($nameValue, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="field">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="field">
            <label for="role">Role</label>
            <select id="role" name="role" required style="width:100%; padding:11px 12px; border-radius:10px; border:1px solid rgba(15,23,42,0.14);">
                <option value="admin" <?php echo $roleValue === 'admin' ? 'selected' : ''; ?>>admin</option>
                <option value="profissional" <?php echo $roleValue === 'profissional' ? 'selected' : ''; ?>>profissional</option>
                <option value="corretor" <?php echo $roleValue === 'corretor' ? 'selected' : ''; ?>>corretor</option>
            </select>
        </div>

        <?php if ($isEdit): ?>
            <div class="field">
                <label for="active">Ativo</label>
                <select id="active" name="active" required style="width:100%; padding:11px 12px; border-radius:10px; border:1px solid rgba(15,23,42,0.14);">
                    <option value="1" <?php echo $activeValue === 1 ? 'selected' : ''; ?>>Sim</option>
                    <option value="0" <?php echo $activeValue === 0 ? 'selected' : ''; ?>>Não</option>
                </select>
            </div>
        <?php endif; ?>

        <div class="field">
            <label for="password"><?php echo $isEdit ? 'Nova senha (opcional)' : 'Senha'; ?></label>
            <input id="password" name="password" type="password" <?php echo $isEdit ? '' : 'required'; ?>>
            <div class="muted" style="font-size:12px; margin-top:6px;">Mínimo 8 caracteres.</div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn" type="submit" style="width:auto; padding:10px 14px;">
                <?php echo $isEdit ? 'Salvar alterações' : 'Criar usuário'; ?>
            </button>
            <a class="link" href="<?php echo htmlspecialchars(APP_URL . '/users', ENT_QUOTES, 'UTF-8'); ?>">Voltar</a>
        </div>
    </form>
</div>
