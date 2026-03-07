<?php
declare(strict_types=1);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-title">Menu</div>
        <div class="sidebar-subtitle"><?php echo htmlspecialchars((string)($_SESSION['user_role'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <nav class="nav">
        <a class="nav-link" href="<?php echo htmlspecialchars(APP_URL . '/dashboard', ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a>
        <a class="nav-link" href="<?php echo htmlspecialchars(APP_URL . '/projects', ENT_QUOTES, 'UTF-8'); ?>">Projetos</a>
        <a class="nav-link" href="<?php echo htmlspecialchars(APP_URL . '/reports/lots', ENT_QUOTES, 'UTF-8'); ?>">Relatórios - Lotes</a>
        <a class="nav-link" href="<?php echo htmlspecialchars(APP_URL . '/reports/reservations', ENT_QUOTES, 'UTF-8'); ?>">Relatórios - Reservas</a>
        <a class="nav-link" href="<?php echo htmlspecialchars(APP_URL . '/reports/sales', ENT_QUOTES, 'UTF-8'); ?>">Relatórios - Vendas</a>
        <?php if (isset($_SESSION['user_role']) && (string)$_SESSION['user_role'] === 'admin'): ?>
            <a class="nav-link" href="<?php echo htmlspecialchars(APP_URL . '/users', ENT_QUOTES, 'UTF-8'); ?>">Usuários</a>
            <a class="nav-link" href="<?php echo htmlspecialchars(APP_URL . '/settings', ENT_QUOTES, 'UTF-8'); ?>">Configurações</a>
        <?php endif; ?>
    </nav>
</aside>
