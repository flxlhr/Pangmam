<?php
/**
 * Einziger Einstiegspunkt der API: /api/?action=<name>
 *
 * Jede Aktion bekommt JSON im Body und antwortet mit JSON
 * ({"ok": true, ...} oder {"ok": false, "error": "..."}).
 * Welche Aktionen es gibt und was sie zurückgeben: docs/api.md
 */

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

Response::sendCorsHeaders($config['allow_origin']);
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit; // CORS-Vorabanfrage des Browsers
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($input)) {
    $input = [];
}

try {
    $lobbies = new LobbyService(Db::connect($config));

    $result = match ($action) {
        'ping' => ['ok' => true, 'message' => 'Pangmam-API läuft'],
        'create_lobby' => $lobbies->createLobby($input['name'] ?? '', (int) ($input['avatar'] ?? 0)),
        'join_lobby' => $lobbies->joinLobby($input['code'] ?? '', $input['name'] ?? '', (int) ($input['avatar'] ?? 0)),
        'leave_lobby' => $lobbies->leaveLobby($input['token'] ?? ''),
        'state' => $lobbies->getState($input['token'] ?? '', (int) ($input['since'] ?? 0)),
        // Neue Aktionen hier eintragen (z. B. 'start_game', 'guess_letter') und in docs/api.md beschreiben.
        default => throw new ApiError("Unbekannte Aktion „{$action}“.", 404),
    };
    Response::json($result);
} catch (ApiError $e) {
    Response::json(['ok' => false, 'error' => $e->getMessage()], $e->status);
} catch (Throwable $e) {
    error_log('Pangmam: ' . $e);
    $message = $config['debug'] ? $e->getMessage() : 'Interner Serverfehler.';
    Response::json(['ok' => false, 'error' => $message], 500);
}
