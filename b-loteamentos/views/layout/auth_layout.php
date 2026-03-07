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
<?php require $viewFile; ?>
</body>
</html>
