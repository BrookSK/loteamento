<div class="row" style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <h1 class="page-title" style="margin:0;">Projetos</h1>
    <?php if (isset($_SESSION['user_role']) && (string)$_SESSION['user_role'] === 'admin'): ?>
        <a class="btn" style="display:inline-block; width:auto; padding:10px 14px;" href="<?php echo htmlspecialchars(APP_URL . '/projects/create', ENT_QUOTES, 'UTF-8'); ?>">Novo projeto</a>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:12px; overflow:auto;">
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">ID</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">Nome</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">Status</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(15,23,42,0.10);">Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (($projects ?? []) as $p): ?>
            <tr>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo htmlspecialchars((string)$p['id'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08);">
                    <?php echo htmlspecialchars((string)$p['status'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(15,23,42,0.08); white-space: nowrap;">
                    <?php if (isset($_SESSION['user_role']) && (string)$_SESSION['user_role'] === 'admin'): ?>
                        <a class="link" href="<?php echo htmlspecialchars(APP_URL . '/projects/' . (int)$p['id'] . '/editor', ENT_QUOTES, 'UTF-8'); ?>">Editor</a>
                    <?php endif; ?>
                    <a class="link" style="margin-left:10px;" href="<?php echo htmlspecialchars(APP_URL . '/projects/' . (int)$p['id'] . '/map', ENT_QUOTES, 'UTF-8'); ?>">Mapa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
