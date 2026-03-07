<?php
declare(strict_types=1);

namespace Controllers;

use Core\Auth;
use Core\Controller;
use Core\Validator;
use Models\UserModel;

final class AuthController extends Controller
{
    public function redirectHome(array $params = []): void
    {
        Auth::init();

        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->redirect('/login');
    }

    public function showLogin(array $params = []): void
    {
        Auth::init();

        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $error = isset($_GET['error']) ? 'E-mail ou senha inválidos.' : null;
        $blocked = isset($_GET['blocked']) ? 'Muitas tentativas. Aguarde e tente novamente.' : null;

        $this->viewAuth('auth/login', [
            'csrfToken' => Auth::csrfToken(),
            'error' => $error,
            'blocked' => $blocked,
            'emailValue' => isset($_GET['email']) ? (string)$_GET['email'] : '',
        ]);
    }

    public function login(array $params = []): void
    {
        Auth::init();

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (!Auth::rateLimitCanAttempt($ip)) {
            $this->redirect('/login?blocked=1');
        }

        $csrf = isset($_POST['csrf']) ? (string)$_POST['csrf'] : null;
        if (!Auth::verifyCsrf($csrf)) {
            http_response_code(419);
            echo 'Token CSRF inválido.';
            exit;
        }

        $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

        if (!Validator::required($email) || !Validator::email($email) || !Validator::required($password)) {
            Auth::rateLimitRegisterFail($ip);
            $this->redirect('/login?error=1&email=' . urlencode($email));
        }

        $model = new UserModel();
        $user = $model->getActiveByEmail(strtolower($email));

        if ($user === false || !isset($user['password']) || !password_verify($password, (string)$user['password'])) {
            Auth::rateLimitRegisterFail($ip);
            $this->redirect('/login?error=1&email=' . urlencode($email));
        }

        Auth::rateLimitClear($ip);
        Auth::loginUser((int)$user['id'], (string)$user['name'], (string)$user['role']);

        $this->redirect('/dashboard');
    }

    public function logout(array $params = []): void
    {
        Auth::init();
        Auth::logout();
        $this->redirect('/login');
    }
}
