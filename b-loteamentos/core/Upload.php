<?php
declare(strict_types=1);

namespace Core;

final class Upload
{
    public static function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }

    public static function handleImage(string $field, string $destDirAbs, int $maxBytes): string
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            throw new \RuntimeException('Arquivo não enviado.');
        }

        $file = $_FILES[$field];
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha no upload.');
        }

        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new \RuntimeException('Tamanho inválido.');
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \RuntimeException('Arquivo temporário inválido.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!is_string($mime) || !in_array($mime, UPLOAD_ALLOWED_IMAGE, true)) {
            throw new \RuntimeException('Tipo de arquivo não permitido.');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };

        self::ensureDir($destDirAbs);

        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $destAbs = rtrim($destDirAbs, '/\\') . DIRECTORY_SEPARATOR . $name;

        if (!move_uploaded_file($tmp, $destAbs)) {
            throw new \RuntimeException('Não foi possível salvar o arquivo.');
        }

        $uploadsRoot = rtrim(UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR;
        $destNorm = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $destAbs);
        $rootNorm = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $uploadsRoot);

        if (str_starts_with($destNorm, $rootNorm)) {
            $relative = 'uploads/' . ltrim(str_replace(DIRECTORY_SEPARATOR, '/', substr($destNorm, strlen($rootNorm))), '/');
            return $relative;
        }

        return 'uploads/' . $name;
    }
}
