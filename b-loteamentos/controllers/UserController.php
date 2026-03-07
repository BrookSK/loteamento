<?php
declare(strict_types=1);

namespace Controllers;

use Core\Auth;
use Core\Controller;
use Core\Validator;
use Models\UserModel;

final class UserController extends Controller
{
    public function index(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $model = new UserModel();
        $users = $model->listAll();

        $flash = isset($_GET['ok']) ? (string)$_GET['ok'] : '';
        $error = isset($_GET['err']) ? (string)$_GET['err'] : '';

        $this->view('users/index', [
            'users' => $users,
            'csrfToken' => Auth::csrfToken(),
            'flash' => $flash,
            'error' => $error,
        ]);
    }

    public function form(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $model = new UserModel();
        $user = null;
        if ($id > 0) {
            $user = $model->getById($id);
            if ($user === false) {
                $this->redirect('/users?err=not_found');
            }
        }

        $error = isset($_GET['err']) ? (string)$_GET['err'] : '';

        $this->view('users/form', [
            'csrfToken' => Auth::csrfToken(),
            'user' => $user,
            'error' => $error,
        ]);
    }

    public function store(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $csrf = isset($_POST['csrf']) ? (string)$_POST['csrf'] : null;
        if (!Auth::verifyCsrf($csrf)) {
            http_response_code(419);
            echo 'Token CSRF inválido.';
            exit;
        }

        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        $email = isset($_POST['email']) ? strtolower(trim((string)$_POST['email'])) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $role = isset($_POST['role']) ? (string)$_POST['role'] : '';

        if (!Validator::required($name) || !Validator::required($email) || !Validator::email($email) || !Validator::minLength($password, 8)) {
            $this->redirect('/users/form?err=invalid');
        }

        if (!in_array($role, ['admin', 'profissional', 'corretor'], true)) {
            $this->redirect('/users/form?err=invalid_role');
        }

        $model = new UserModel();
        if ($model->existsEmail($email)) {
            $this->redirect('/users/form?err=email_exists');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $model->create($name, $email, $hash, $role);

        $this->redirect('/users?ok=created');
    }

    public function update(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $csrf = isset($_POST['csrf']) ? (string)$_POST['csrf'] : null;
        if (!Auth::verifyCsrf($csrf)) {
            http_response_code(419);
            echo 'Token CSRF inválido.';
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        $email = isset($_POST['email']) ? strtolower(trim((string)$_POST['email'])) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $role = isset($_POST['role']) ? (string)$_POST['role'] : '';
        $active = isset($_POST['active']) ? (int)$_POST['active'] : 1;

        if ($id <= 0 || !Validator::required($name) || !Validator::required($email) || !Validator::email($email)) {
            $this->redirect('/users?err=invalid');
        }

        if (!in_array($role, ['admin', 'profissional', 'corretor'], true)) {
            $this->redirect('/users/form?id=' . $id . '&err=invalid_role');
        }

        if (!in_array($active, [0, 1], true)) {
            $active = 1;
        }

        $model = new UserModel();
        if ($model->existsEmail($email, $id)) {
            $this->redirect('/users/form?id=' . $id . '&err=email_exists');
        }

        $model->updateUser($id, $name, $email, $role);
        $model->setActive($id, $active);

        if ($password !== '') {
            if (!Validator::minLength($password, 8)) {
                $this->redirect('/users/form?id=' . $id . '&err=weak_password');
            }
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $model->updatePassword($id, $hash);
        }

        $this->redirect('/users?ok=updated');
    }

    public function delete(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $csrf = isset($_POST['csrf']) ? (string)$_POST['csrf'] : null;
        if (!Auth::verifyCsrf($csrf)) {
            http_response_code(419);
            echo 'Token CSRF inválido.';
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            $this->redirect('/users?err=invalid');
        }

        $model = new UserModel();
        $model->setActive($id, 0);

        $this->redirect('/users?ok=deactivated');
    }
}
