<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/Upload.php';
require_once __DIR__ . '/core/VectorizerAPI.php';

require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/models/SettingsModel.php';
require_once __DIR__ . '/models/ProjectModel.php';
require_once __DIR__ . '/models/LotModel.php';
require_once __DIR__ . '/models/HistoryModel.php';

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/SettingsController.php';
require_once __DIR__ . '/controllers/ProjectController.php';
require_once __DIR__ . '/controllers/ReportsController.php';

use Core\Router;
use Core\Auth;

$router = new Router();

Auth::init();

$router->get('/', ['AuthController', 'redirectHome']);
$router->get('/login', ['AuthController', 'showLogin']);
$router->post('/login', ['AuthController', 'login']);
$router->get('/logout', ['AuthController', 'logout']);

$router->get('/dashboard', ['DashboardController', 'index']);

$router->get('/reports/lots', ['ReportsController', 'lots']);
$router->get('/reports/lots.csv', ['ReportsController', 'lotsCsv']);
$router->get('/reports/reservations', ['ReportsController', 'reservations']);
$router->get('/reports/reservations.csv', ['ReportsController', 'reservationsCsv']);
$router->get('/reports/sales', ['ReportsController', 'sales']);
$router->get('/reports/sales.csv', ['ReportsController', 'salesCsv']);

$router->get('/projects', ['ProjectController', 'index']);
$router->get('/projects/create', ['ProjectController', 'create']);
$router->post('/projects/store', ['ProjectController', 'store']);
$router->get('/projects/{id}/editor', ['ProjectController', 'editor']);
$router->get('/projects/{id}/map', ['ProjectController', 'map']);

$router->get('/users', ['UserController', 'index']);
$router->get('/users/form', ['UserController', 'form']);
$router->post('/users/store', ['UserController', 'store']);
$router->post('/users/update', ['UserController', 'update']);
$router->post('/users/delete', ['UserController', 'delete']);

$router->get('/settings', ['SettingsController', 'index']);
$router->post('/settings/update', ['SettingsController', 'update']);

$router->dispatch();
