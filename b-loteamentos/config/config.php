<?php
declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'b_loteamentos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'B Loteamentos');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/b-loteamentos');
define('APP_PATH', __DIR__ . '/../');

define('UPLOAD_MAX_SIZE', 20 * 1024 * 1024);
define('UPLOAD_PATH', APP_PATH . 'uploads/');
define('UPLOAD_ALLOWED_IMAGE', ['image/jpeg', 'image/png', 'image/webp']);

define('VECTORIZER_API_ID', '');
define('VECTORIZER_API_SECRET', '');
define('VECTORIZER_ENDPOINT', 'https://vectorizer.ai/api/v1/vectorize');

define('RESERVATION_HOURS', 48);

define('SESSION_LIFETIME', 14400);

define('CSRF_KEY', 'csrf_token');

define('LOGIN_RATE_LIMIT_MAX_ATTEMPTS', 5);
define('LOGIN_RATE_LIMIT_WINDOW', 900);
