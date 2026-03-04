<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
if (isset($_GET['error'])) {
    $error = 'E-mail ou senha inválidos.';
}

$flash = null;
if (isset($_GET['logged_out'])) {
    $flash = 'Você saiu do sistema.';
}

$emailValue = '';
if (isset($_GET['email'])) {
    $emailValue = (string)$_GET['email'];
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <h1 class="title">Entrar</h1>
            <p class="subtitle">Acesse sua conta para continuar.</p>
            <?php if ($error !== null): ?>
                <div class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php elseif ($flash !== null): ?>
                <div class="alert" style="border-color: rgba(106,167,255,0.35); background: rgba(106,167,255,0.10);">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
        </div>

        <form class="form" method="post" action="authenticate.php" autocomplete="on">
            <div>
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="voce@empresa.com">
            </div>
            <div>
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" required placeholder="Sua senha">
            </div>
            <button class="btn" type="submit">Entrar</button>
            <div class="small">Usuário demo: <code>admin@local</code> | senha: <code>admin123</code></div>
        </form>
    </div>
</div>
</body>
</html>
