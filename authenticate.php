<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if ($email === '' || $password === '') {
    header('Location: login.php?error=1&email=' . urlencode($email));
    exit;
}

$users = [
    'admin@local' => [
        'id' => 1,
        'name' => 'Administrador',
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    ],
];

$user = $users[strtolower($email)] ?? null;

if ($user === null || !password_verify($password, $user['password_hash'])) {
    header('Location: login.php?error=1&email=' . urlencode($email));
    exit;
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => strtolower($email),
];

header('Location: dashboard.php');
exit;
