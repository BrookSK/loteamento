<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="topbar">
        <div class="row" style="max-width: 980px; margin: 0 auto; padding: 0 4px;">
            <div class="badge">
                Logado como <strong style="color: rgba(255,255,255,0.92);">&nbsp;<?php echo htmlspecialchars((string)$user['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <div class="page">
        <h1 style="margin-top: 0;">Dashboard</h1>
        <p style="color: rgba(255,255,255,0.74); line-height: 1.5;">
            Esta é uma página protegida por sessão. Se você tentar acessar sem login, será redirecionado para <code>login.php</code>.
        </p>
        <div class="card" style="max-width: 980px;">
            <div class="subtitle">Detalhes da sessão</div>
            <pre style="margin: 12px 0 0; white-space: pre-wrap; color: rgba(255,255,255,0.86);">
<?php
echo htmlspecialchars(json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
?>
            </pre>
        </div>
    </div>
</body>
</html>
