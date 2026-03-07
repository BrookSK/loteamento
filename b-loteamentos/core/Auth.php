<?php
declare(strict_types=1);

namespace Core;

final class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::enforceIdleTimeout();
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        self::enforceIdleTimeout();
    }

    private static function enforceIdleTimeout(): void
    {
        $now = time();
        $lastActivity = (int)($_SESSION['last_activity'] ?? 0);

        if ($lastActivity > 0 && ($now - $lastActivity) > SESSION_LIFETIME) {
            self::logout();
            session_start();
        }

        $_SESSION['last_activity'] = $now;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id'], $_SESSION['user_role']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        $role = (string)($_SESSION['user_role'] ?? '');
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            echo '403 - Acesso negado';
            exit;
        }
    }

    public static function loginUser(int $userId, string $userName, string $userRole): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['last_activity'] = time();
    }

    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool)$params['secure'],
                (bool)$params['httponly']
            );
        }

        session_destroy();
    }

    public static function csrfToken(): string
    {
        if (!isset($_SESSION[CSRF_KEY]) || !is_string($_SESSION[CSRF_KEY]) || $_SESSION[CSRF_KEY] === '') {
            $_SESSION[CSRF_KEY] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION[CSRF_KEY];
    }

    public static function verifyCsrf(?string $token): bool
    {
        $sessionToken = $_SESSION[CSRF_KEY] ?? null;
        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        if (!is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function rateLimitCanAttempt(string $ip): bool
    {
        $data = self::rateLimitRead($ip);
        if ($data === null) {
            return true;
        }

        $first = (int)($data['first'] ?? 0);
        $count = (int)($data['count'] ?? 0);

        if ($first <= 0) {
            return true;
        }

        if ((time() - $first) > LOGIN_RATE_LIMIT_WINDOW) {
            self::rateLimitDelete($ip);
            return true;
        }

        return $count < LOGIN_RATE_LIMIT_MAX_ATTEMPTS;
    }

    public static function rateLimitRegisterFail(string $ip): void
    {
        $data = self::rateLimitRead($ip);
        $now = time();

        if ($data === null || (int)($data['first'] ?? 0) <= 0 || ($now - (int)$data['first']) > LOGIN_RATE_LIMIT_WINDOW) {
            self::rateLimitWrite($ip, ['first' => $now, 'count' => 1]);
            return;
        }

        $count = (int)($data['count'] ?? 0);
        $count++;
        self::rateLimitWrite($ip, ['first' => (int)$data['first'], 'count' => $count]);
    }

    public static function rateLimitClear(string $ip): void
    {
        self::rateLimitDelete($ip);
    }

    private static function rateLimitFile(string $ip): string
    {
        $key = md5($ip);
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'b_loteamentos_login_' . $key . '.json';
    }

    private static function rateLimitRead(string $ip): ?array
    {
        $file = self::rateLimitFile($ip);
        if (!is_file($file)) {
            return null;
        }

        $raw = file_get_contents($file);
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private static function rateLimitWrite(string $ip, array $data): void
    {
        $file = self::rateLimitFile($ip);
        @file_put_contents($file, json_encode($data));
    }

    private static function rateLimitDelete(string $ip): void
    {
        $file = self::rateLimitFile($ip);
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
