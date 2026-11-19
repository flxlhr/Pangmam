<?php

declare(strict_types=1);

/**
 * Hilfsfunktionen zum Antworten: JSON zurückschicken und CORS-Header setzen.
 */
final class Response
{
    /**
     * Schickt $data als JSON an das Spiel.
     *
     * @param array $data   Die Antwort, z. B. ['ok' => true, 'code' => 'K7QX'].
     * @param int   $status HTTP-Statuscode (200 = alles in Ordnung).
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        // Dem Empfänger sagen, dass JSON in UTF-8 kommt (wichtig für Umlaute).
        header('Content-Type: application/json; charset=utf-8');
        // Antworten nie zwischenspeichern – der Spielstand ändert sich ständig.
        header('Cache-Control: no-store');
        // Umlaute und "/" nicht maskieren, damit die Antwort lesbar bleibt.
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Nur lokal nötig: Godots „Im Browser ausführen“ läuft auf einem anderen Port als die API. */
    public static function sendCorsHeaders(?string $allowOrigin): void
    {
        // null = CORS aus (auf dem Webspace liegen Spiel und API unter derselben Adresse).
        if ($allowOrigin === null) {
            return;
        }
        // Welche Adressen die API aufrufen dürfen ("*" = alle).
        header('Access-Control-Allow-Origin: ' . $allowOrigin);
        // Welche HTTP-Methoden erlaubt sind.
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        // Welche Header das Spiel mitschicken darf.
        header('Access-Control-Allow-Headers: Content-Type');
    }
}
