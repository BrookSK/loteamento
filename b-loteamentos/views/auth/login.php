<div class="auth-bg">
    <div class="auth-card">
        <h1 class="auth-title">Entrar</h1>
        <p class="auth-subtitle">Acesse sua conta para continuar.</p>

        <?php if (isset($blocked) && is_string($blocked) && $blocked !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($blocked, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif (isset($error) && is_string($error) && $error !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form class="form" method="post" action="<?php echo htmlspecialchars(APP_URL . '/login', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="on">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars((string)$emailValue, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="field">
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" required>
            </div>

            <button class="btn" type="submit">Entrar</button>
        </form>
    </div>
</div>
