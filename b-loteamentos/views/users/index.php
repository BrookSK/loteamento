<div class="row" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <h1 class="page-title" style="margin:0;">Usuários</h1>
    <a class="btn" style="display:inline-block; width:auto; padding:10px 14px;" href="<?php echo htmlspecialchars(APP_URL . '/users/form', ENT_QUOTES, 'UTF-8'); ?>">Novo usuário</a>
</div>

<?php if (isset($flash) && $flash !== ''): ?>
    <div class="card" style="margin-top:12px; border-color: rgba(37,99,235,0.22);">
        <div class="muted">Ação realizada: <?php echo htmlspecialchars((string)$flash, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php endif; ?>

<?php if (isset($error) && $error !== ''): ?>
    <div class="alert" style="margin-top:12px;">Erro: <?php echo htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="card" style="margin-top:12px; overflow:auto;">
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">ID</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">Nome</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">E-mail</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">Role</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">Ativo</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (($users ?? []) as $u): ?>
            <tr>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo htmlspecialchars((string)$u['id'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo htmlspecialchars((string)$u['name'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo htmlspecialchars((string)$u['email'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo htmlspecialchars((string)$u['role'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo ((int)$u['active'] === 1) ? 'Sim' : 'Não'; ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08); white-space: nowrap;">
                    <a class="link" href="<?php echo htmlspecialchars(APP_URL . '/users/form?id=' . (int)$u['id'], ENT_QUOTES, 'UTF-8'); ?>">Editar</a>
                    <form method="post" action="<?php echo htmlspecialchars(APP_URL . '/users/delete', ENT_QUOTES, 'UTF-8'); ?>" style="display:inline; margin-left:10px;" onsubmit="return confirm('Inativar este usuário?');">
                        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$u['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="link" style="background:none; border:0; padding:0; cursor:pointer; color:#ef4444;">Inativar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
