<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(APP_URL . '/assets/css/app.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<div class="app">
    <?php require APP_PATH . 'views/layout/sidebar.php'; ?>
    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <div class="brand"><?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="topbar-right">
                <a class="link" href="<?php echo htmlspecialchars(APP_URL . '/logout', ENT_QUOTES, 'UTF-8'); ?>">Sair</a>
            </div>
        </header>
        <div class="content">
