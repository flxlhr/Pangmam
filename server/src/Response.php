<?php

declare(strict_types=1);

final class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Nur lokal nötig: Godots „Im Browser ausführen“ läuft auf einem anderen Port als die API. */
    public static function sendCorsHeaders(?string $allowOrigin): void
    {
        if ($allowOrigin === null) {
            return;
        }
        header('Access-Control-Allow-Origin: ' . $allowOrigin);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
}
