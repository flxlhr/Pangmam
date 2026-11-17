<?php
/**
 * Einziger Einstiegspunkt der API: /api/?action=<name>
 *
 * Jede Aktion bekommt JSON im Body und antwortet mit JSON
 * ({"ok": true, ...} oder {"ok": false, "error": "..."}).
 * Welche Aktionen es gibt und was sie zurückgeben: docs/api.md
 *
 * Ablauf einer Anfrage:
 *   1. Konfiguration und Klassen laden (bootstrap.php)
 *   2. CORS-Header senden (nur lokal nötig)
 *   3. Aktion aus der URL und Daten aus dem Body lesen
 *   4. Passende Methode im LobbyService aufrufen
 *   5. Ergebnis oder Fehlermeldung als JSON zurückschicken
 */

// Strenge Typprüfung: PHP wandelt z. B. "5" nicht still in 5 um, sondern meldet einen Fehler.
declare(strict_types=1);

// Lädt $config und alle Klassen (ApiError, Response, Db, LobbyService).
require __DIR__ . '/../src/bootstrap.php';

// CORS-Header erlauben dem Spiel, die API von einer anderen Adresse (z. B. anderem Port) aufzurufen.
Response::sendCorsHeaders($config['allow_origin']);
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit; // CORS-Vorabanfrage des Browsers
}

// Welche Aktion gewünscht ist, steht in der URL: ?action=create_lobby
$action = $_GET['action'] ?? '';
// Die eigentlichen Daten (Name, Code, Token, …) kommen als JSON im Body der Anfrage.
// json_decode(..., true) macht daraus ein assoziatives PHP-Array.
$input = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($input)) {
    // Kaputtes oder fehlendes JSON → so tun, als wären keine Daten mitgeschickt worden.
    $input = [];
}

try {
    // Datenbankverbindung öffnen und an den LobbyService übergeben, der die eigentliche Logik enthält.
    $lobbies = new LobbyService(Db::connect($config));

    // match ordnet jedem Aktionsnamen den passenden Methodenaufruf zu (wie ein switch, nur kürzer).
    // "?? ''" bzw. "?? 0" setzt einen Standardwert, falls das Feld im JSON fehlt.
    $result = match ($action) {
        // Einfacher Test, ob die API überhaupt erreichbar ist.
        'ping' => ['ok' => true, 'message' => 'Pangmam-API läuft'],
        // Neue Lobby anlegen; der Ersteller wird automatisch Host.
        'create_lobby' => $lobbies->createLobby($input['name'] ?? '', (int) ($input['avatar'] ?? 0)),
        // Mit einem Lobby-Code einer bestehenden Lobby beitreten.
        'join_lobby' => $lobbies->joinLobby($input['code'] ?? '', $input['name'] ?? '', (int) ($input['avatar'] ?? 0)),
        // Lobby verlassen; der Spieler wird gelöscht.
        'leave_lobby' => $lobbies->leaveLobby($input['token'] ?? ''),
        // Aktuellen Spielstand abfragen (wird vom Spiel jede Sekunde aufgerufen).
        'state' => $lobbies->getState($input['token'] ?? '', (int) ($input['since'] ?? 0)),
        // Neue Aktionen hier eintragen (z. B. 'start_game', 'guess_letter') und in docs/api.md beschreiben.
        // Unbekannter Aktionsname → Fehler 404 ("nicht gefunden").
        default => throw new ApiError("Unbekannte Aktion „{$action}“.", 404),
    };
    // Erfolgreiches Ergebnis als JSON an das Spiel schicken.
    Response::json($result);
} catch (ApiError $e) {
    // Erwarteter Fehler (z. B. „Lobby ist voll“): Meldung geht direkt an die Spieler.
    Response::json(['ok' => false, 'error' => $e->getMessage()], $e->status);
} catch (Throwable $e) {
    // Unerwarteter Fehler (Programmfehler, Datenbank weg, …): ins Server-Log schreiben.
    error_log('Pangmam: ' . $e);
    // Details nur im Debug-Modus (lokal) zeigen – online sollen Spieler keine Interna sehen.
    $message = $config['debug'] ? $e->getMessage() : 'Interner Serverfehler.';
    Response::json(['ok' => false, 'error' => $message], 500);
}
