<?php
declare(strict_types=1);

namespace Controllers;

use Core\Auth;
use Core\Controller;
use Models\SettingsModel;

final class SettingsController extends Controller
{
    public function index(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $model = new SettingsModel();
        $settings = $model->getMany([
            'vectorizer_api_id',
            'vectorizer_api_secret',
            'reservation_hours',
        ]);

        $flash = isset($_GET['ok']) ? (string)$_GET['ok'] : '';

        $this->view('settings/index', [
            'csrfToken' => Auth::csrfToken(),
            'settings' => $settings,
            'flash' => $flash,
        ]);
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

        $apiId = isset($_POST['vectorizer_api_id']) ? trim((string)$_POST['vectorizer_api_id']) : '';
        $apiSecret = isset($_POST['vectorizer_api_secret']) ? trim((string)$_POST['vectorizer_api_secret']) : '';
        $reservationHours = isset($_POST['reservation_hours']) ? trim((string)$_POST['reservation_hours']) : '';

        if ($reservationHours === '' || !ctype_digit($reservationHours) || (int)$reservationHours <= 0) {
            $reservationHours = (string)RESERVATION_HOURS;
        }

        $model = new SettingsModel();
        $model->set('vectorizer_api_id', $apiId !== '' ? $apiId : null);
        $model->set('vectorizer_api_secret', $apiSecret !== '' ? $apiSecret : null);
        $model->set('reservation_hours', $reservationHours);

        $this->redirect('/settings?ok=updated');
    }
}
