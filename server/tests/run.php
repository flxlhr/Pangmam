<?php
/**
 * Einfache Tests ohne zusätzliche Werkzeuge. Starten mit:  php server/tests/run.php
 *
 * Neuen Test anlegen: unten test('Beschreibung', function () { ... }); ergänzen
 * und darin check(<Bedingung>, '<was erwartet wurde>') aufrufen.
 * Jeder Test bekommt eine frische Datenbank im Arbeitsspeicher.
 */

declare(strict_types=1);

// Klassen laden (LobbyService, Db, ApiError, …) – genau wie die echte API.
require __DIR__ . '/../src/bootstrap.php';

// Zähler für die Zusammenfassung am Ende.
$failures = 0;
$count = 0;

/**
 * Führt einen Test aus und gibt ✓ (bestanden) oder ✗ (fehlgeschlagen) aus.
 * Ein Test gilt als fehlgeschlagen, sobald darin irgendein Fehler geworfen wird.
 */
function test(string $name, callable $body): void
{
    // "global" holt die Zähler von oben in die Funktion hinein.
    global $failures, $count;
    $count++;
    try {
        $body();
        echo "  ✓ {$name}\n";
    } catch (Throwable $e) {
        $failures++;
        echo "  ✗ {$name}\n      {$e->getMessage()}\n";
    }
}

/** Prüft eine Bedingung. Ist sie falsch, schlägt der aktuelle Test mit $expectation als Meldung fehl. */
function check(bool $condition, string $expectation): void
{
    if (!$condition) {
        throw new RuntimeException('Erwartet: ' . $expectation);
    }
}

/** Erwartet, dass $body einen ApiError wirft. Passiert das nicht, schlägt der Test fehl. */
function expectError(callable $body, string $expectation): void
{
    try {
        $body();
    } catch (ApiError) {
        // Der erwartete Fehler kam → alles gut.
        return;
    }
    throw new RuntimeException('Erwartet: ' . $expectation);
}

/** Erzeugt einen LobbyService mit einer leeren Datenbank im Arbeitsspeicher (keine Datei, nach dem Test weg). */
function freshService(): LobbyService
{
    $pdo = new PDO('sqlite::memory:');
    // Gleiche Einstellungen wie in Db::connect().
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    Db::createSchema($pdo);
    return new LobbyService($pdo);
}

// ---------------------------------------------------------------------------
// Tests für Lobbys
// ---------------------------------------------------------------------------

echo "Lobby\n";

test('Lobby erstellen macht den Ersteller zum Host', function () {
    $s = freshService();
    $res = $s->createLobby('Felix', 3);
    check($res['ok'] && strlen($res['code']) === 4, 'ok und 4-stelliger Code');
    check($res['state']['lobby']['host_id'] === $res['player_id'], 'Ersteller ist Host');
    check($res['state']['players'][0]['avatar'] === 3, 'Avatar wird übernommen');
});

test('Beitreten zeigt beide Spieler', function () {
    $s = freshService();
    $host = $s->createLobby('Felix', 0);
    // Code absichtlich klein geschrieben – muss trotzdem funktionieren.
    $s->joinLobby(strtolower($host['code']), 'Lara', 1);
    $state = $s->getState($host['token'], 0)['state'];
    check(count($state['players']) === 2, '2 Spieler');
    check($state['players'][1]['name'] === 'Lara', 'Lara als Zweite');
});

test('Name ist Pflicht und höchstens 16 Zeichen', function () {
    $s = freshService();
    expectError(fn() => $s->createLobby('   ', 0), 'Fehler bei leerem Namen');
    expectError(fn() => $s->createLobby(str_repeat('x', 17), 0), 'Fehler bei 17 Zeichen');
});

test('Gleicher Name in einer Lobby wird abgelehnt', function () {
    $s = freshService();
    $host = $s->createLobby('Felix', 0);
    // "felix" klein geschrieben gilt trotzdem als derselbe Name.
    expectError(fn() => $s->joinLobby($host['code'], 'felix', 0), 'Fehler bei doppeltem Namen');
});

test('Höchstens 8 Spieler pro Lobby', function () {
    $s = freshService();
    $host = $s->createLobby('Spieler 1', 0);
    // Spieler 2 bis 8 treten bei → Lobby ist voll.
    for ($i = 2; $i <= 8; $i++) {
        $s->joinLobby($host['code'], "Spieler {$i}", 0);
    }
    expectError(fn() => $s->joinLobby($host['code'], 'Spieler 9', 0), 'Fehler beim 9. Spieler');
});

test('Unbekannter Code wird abgelehnt', function () {
    $s = freshService();
    expectError(fn() => $s->joinLobby('ZZZZ', 'Lara', 0), 'Fehler bei falschem Code');
});

test('Ohne Änderung meldet state "changed: false"', function () {
    $s = freshService();
    $host = $s->createLobby('Felix', 0);
    $version = $host['state']['version'];
    check($s->getState($host['token'], $version)['changed'] === false, 'keine Änderung');
    // Ein Beitritt erhöht die Version → jetzt muss "changed: true" kommen.
    $s->joinLobby($host['code'], 'Lara', 0);
    check($s->getState($host['token'], $version)['changed'] === true, 'Änderung nach Beitritt');
});

test('Geht der Host, übernimmt der nächste Spieler', function () {
    $s = freshService();
    $host = $s->createLobby('Felix', 0);
    $lara = $s->joinLobby($host['code'], 'Lara', 0);
    $s->leaveLobby($host['token']);
    $state = $s->getState($lara['token'], 0)['state'];
    check($state['lobby']['host_id'] === $lara['player_id'], 'Lara ist neuer Host');
    // Felix hat die Lobby verlassen → sein Token darf nicht mehr funktionieren.
    expectError(fn() => $s->getState($host['token'], 0), 'alter Token ist ungültig');
});

// Zusammenfassung ausgeben. Der Exit-Code (0 = alles ok, 1 = Fehler) wird von der CI auf GitHub ausgewertet.
echo "\n" . ($failures === 0 ? "Alle {$count} Tests bestanden.\n" : "{$failures} von {$count} Tests fehlgeschlagen.\n");
exit($failures === 0 ? 0 : 1);
