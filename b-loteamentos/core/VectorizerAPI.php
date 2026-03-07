<?php
declare(strict_types=1);

namespace Core;

use Models\SettingsModel;

final class VectorizerAPI
{
    public function vectorize(string $filepath, array $svgAttributes = []): string
    {
        if (!is_file($filepath)) {
            throw new \RuntimeException('Arquivo não encontrado para vetorização.');
        }

        $settings = new SettingsModel();
        $apiId = $settings->get('vectorizer_api_id');
        $apiSecret = $settings->get('vectorizer_api_secret');

        if ($apiId === null || $apiSecret === null || $apiId === '' || $apiSecret === '') {
            throw new \RuntimeException('Chaves da Vectorizer não configuradas em /settings.');
        }

        $ch = curl_init();
        if ($ch === false) {
            throw new \RuntimeException('Falha ao inicializar cURL.');
        }

        $postFields = [
            'image' => new \CURLFile($filepath),
            'output.file_format' => 'svg',
            'processing.max_colors' => '16',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => VECTORIZER_ENDPOINT,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERPWD => $apiId . ':' . $apiSecret,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        ]);

        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo !== 0) {
            throw new \RuntimeException('Erro ao chamar Vectorizer: ' . $err);
        }

        if (!is_string($response) || $response === '') {
            throw new \RuntimeException('Resposta vazia da Vectorizer.');
        }

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('Vectorizer retornou HTTP ' . $status);
        }

        return $this->injectAttributesOnRootSvg($response, $svgAttributes);
    }

    private function injectAttributesOnRootSvg(string $svg, array $attributes): string
    {
        $attributes = array_filter($attributes, static fn($v) => $v !== null);

        if ($attributes === []) {
            return $svg;
        }

        $pairs = [];
        foreach ($attributes as $k => $v) {
            if (!is_string($k) || $k === '') {
                continue;
            }
            $pairs[] = $k . '="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($pairs === []) {
            return $svg;
        }

        $injection = ' ' . implode(' ', $pairs);

        if (preg_match('/<svg\b[^>]*>/i', $svg, $m, PREG_OFFSET_CAPTURE) !== 1) {
            return $svg;
        }

        $fullTag = $m[0][0];
        $pos = (int)$m[0][1];
        $len = strlen($fullTag);

        if (str_contains($fullTag, 'data-bloteamentos-source=')) {
            return $svg;
        }

        $newTag = rtrim(substr($fullTag, 0, -1)) . $injection . '>';
        return substr($svg, 0, $pos) . $newTag . substr($svg, $pos + $len);
    }
}
