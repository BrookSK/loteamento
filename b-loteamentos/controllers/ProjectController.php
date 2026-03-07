<?php
declare(strict_types=1);

namespace Controllers;

use Core\Auth;
use Core\Controller;
use Core\Upload;
use Core\Validator;
use Core\VectorizerAPI;
use Models\ProjectModel;

final class ProjectController extends Controller
{
    public function index(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $model = new ProjectModel();
        $projects = $model->listAll();

        $this->view('projects/index', [
            'projects' => $projects,
        ]);
    }

    public function create(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $error = isset($_GET['err']) ? (string)$_GET['err'] : '';

        $this->view('projects/create', [
            'csrfToken' => Auth::csrfToken(),
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
        $location = isset($_POST['location']) ? trim((string)$_POST['location']) : '';
        $description = isset($_POST['description']) ? trim((string)$_POST['description']) : '';

        if (!Validator::required($name)) {
            $this->redirect('/projects/create?err=invalid');
        }

        try {
            $coverPath = Upload::handleImage('cover_image', UPLOAD_PATH . 'covers/', 5 * 1024 * 1024);
            $originalPath = Upload::handleImage('original_image', UPLOAD_PATH . 'originals/', UPLOAD_MAX_SIZE);
        } catch (\RuntimeException $e) {
            $this->redirect('/projects/create?err=upload');
        }

        $model = new ProjectModel();
        $projectId = $model->create([
            'name' => $name,
            'location' => $location !== '' ? $location : null,
            'description' => $description !== '' ? $description : null,
            'cover_image' => $coverPath,
            'original_image' => $originalPath,
            'status' => 'draft',
            'created_by' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
        ]);

        try {
            Upload::ensureDir(UPLOAD_PATH . 'svg/');

            $vectorizer = new VectorizerAPI();
            $svg = $vectorizer->vectorize(
                APP_PATH . ltrim($originalPath, '/'),
                [
                    'data-bloteamentos-source' => 'vectorizer',
                    'data-project-id' => (string)$projectId,
                ]
            );

            $svgFile = 'uploads/svg/' . $projectId . '.svg';
            file_put_contents(APP_PATH . $svgFile, $svg);

            $model->updateVectorData($projectId, $svgFile, $svg);
        } catch (\Throwable $e) {
            $this->redirect('/projects/' . $projectId . '/editor?err=vectorize');
        }

        $this->redirect('/projects/' . $projectId . '/editor');
    }

    public function editor(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin']);

        $id = isset($params['id']) ? (int)$params['id'] : 0;
        if ($id <= 0) {
            http_response_code(404);
            echo 'Projeto não encontrado.';
            exit;
        }

        $model = new ProjectModel();
        $project = $model->getById($id);
        if ($project === false) {
            http_response_code(404);
            echo 'Projeto não encontrado.';
            exit;
        }

        $this->view('projects/editor', [
            'project' => $project,
        ]);
    }

    public function map(array $params = []): void
    {
        Auth::init();
        Auth::requireRole(['admin','profissional','corretor']);

        $id = isset($params['id']) ? (int)$params['id'] : 0;
        if ($id <= 0) {
            http_response_code(404);
            echo 'Projeto não encontrado.';
            exit;
        }

        $model = new ProjectModel();
        $project = $model->getById($id);
        if ($project === false) {
            http_response_code(404);
            echo 'Projeto não encontrado.';
            exit;
        }

        $this->view('projects/map', [
            'project' => $project,
        ]);
    }
}
