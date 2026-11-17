<?php

declare(strict_types=1);

/**
 * Lobbys und Spieler: erstellen, beitreten, verlassen, Spielstand abfragen.
 *
 * Jeder Spieler bekommt beim Beitreten eine geheime "token". Damit weist er sich
 * bei allen weiteren Anfragen aus – so kann niemand im Namen eines anderen raten.
 *
 * Jede Änderung an einer Lobby erhöht ihre "version". Die Spiele fragen jede Sekunde
 * mit der zuletzt gesehenen Version nach und bekommen nur bei Änderungen den neuen Stand.
 */
final class LobbyService
{
    /** Spielregeln, mit denen jede neue Lobby startet (werden als JSON in lobbies.settings gespeichert). */
    public const DEFAULT_SETTINGS = [
        'max_players' => 8,
        'rounds' => 1,          // Durchgänge: jeder ist einmal Wortgeber
        'max_mistakes' => 10,   // Hangman-Teile, Gestell ist schon vorgezeichnet
    ];
    /** So viele Spieler braucht man mindestens für eine Runde. */
    public const MIN_PLAYERS = 2;
    /** Anzahl wählbarer Avatare (Nummern 0 bis 11). */
    public const AVATAR_COUNT = 12;
    /** Wer so viele Sekunden nicht mehr nachgefragt hat, gilt als „weg“. */
    public const TIMEOUT_SECONDS = 10;
    /** Ohne 0/O und 1/I, damit man Codes gut vorlesen kann. */
    private const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    /** Länge eines Lobby-Codes, z. B. "K7QX". */
    private const CODE_LENGTH = 4;

    /**
     * "private readonly PDO $db" legt automatisch die Eigenschaft $this->db an
     * (Constructor Property Promotion) – deshalb ist der Rumpf leer.
     */
    public function __construct(private readonly PDO $db)
    {
    }

    /**
     * Legt eine neue Lobby an und macht den Ersteller zum Host.
     *
     * @return array Code, Spieler-ID, Token und der erste Spielstand (siehe enterResult()).
     */
    public function createLobby(string $name, int $avatar): array
    {
        $name = $this->checkName($name);
        $code = $this->newLobbyCode();
        // Der Ersteller ist der erste Spieler der Lobby (join_order = 1).
        $player = $this->newPlayer($code, $name, $this->checkAvatar($avatar), 1);

        // Transaktion: Lobby und Spieler werden entweder beide gespeichert oder keiner.
        $this->db->beginTransaction();
        // Die "?" sind Platzhalter – die Werte kommen über execute() und sind so vor SQL-Injection geschützt.
        $this->db->prepare(
            'INSERT INTO lobbies (code, host_id, phase, settings, version, updated_at) VALUES (?, ?, ?, ?, 1, ?)'
        )->execute([$code, $player['id'], 'lobby', json_encode(self::DEFAULT_SETTINGS), time()]);
        $this->insertPlayer($player);
        $this->db->commit();

        return $this->enterResult($player);
    }

    /**
     * Fügt einen neuen Spieler zu einer bestehenden Lobby hinzu.
     *
     * @throws ApiError wenn der Code falsch ist, das Spiel schon läuft, die Lobby voll oder der Name vergeben ist.
     */
    public function joinLobby(string $code, string $name, int $avatar): array
    {
        // Code vereinheitlichen: Leerzeichen weg, Großbuchstaben ("k7qx " → "K7QX").
        $code = strtoupper(trim($code));
        $name = $this->checkName($name);

        $this->db->beginTransaction();
        try {
            // Lobby suchen; gibt es sie nicht, wird sofort ein Fehler geworfen.
            $lobby = $this->findLobby($code) ?? throw new ApiError('Diese Lobby gibt es nicht (mehr).', 404);
            // Beitreten nur, solange noch keine Runde läuft.
            if ($lobby['phase'] !== 'lobby') {
                throw new ApiError('Das Spiel läuft schon. Warte, bis es vorbei ist.', 409);
            }
            $players = $this->playersOf($code);
            $settings = json_decode($lobby['settings'], true);
            // Ist die Lobby schon voll?
            if (count($players) >= $settings['max_players']) {
                throw new ApiError('Die Lobby ist voll.', 409);
            }
            // Namen ohne Rücksicht auf Groß-/Kleinschreibung vergleichen ("Felix" = "felix").
            foreach ($players as $other) {
                if (mb_strtolower($other['name']) === mb_strtolower($name)) {
                    throw new ApiError('Der Name ist in dieser Lobby schon vergeben.', 409);
                }
            }
            // Der Neue kommt hinter den bisher Letzten (höchste join_order + 1).
            $joinOrder = $players === [] ? 1 : max(array_column($players, 'join_order')) + 1;
            $player = $this->newPlayer($code, $name, $this->checkAvatar($avatar), (int) $joinOrder);
            $this->insertPlayer($player);
            // Version erhöhen, damit die anderen Spieler den Neuen beim nächsten Polling sehen.
            $this->bumpVersion($code);
            $this->db->commit();
        } catch (Throwable $e) {
            // Bei jedem Fehler alle Änderungen dieser Transaktion rückgängig machen
            // und den Fehler weiterwerfen (api/index.php kümmert sich um die Antwort).
            $this->db->rollBack();
            throw $e;
        }
        return $this->enterResult($player);
    }

    /**
     * Entfernt den Spieler mit diesem Token aus seiner Lobby.
     * War er der Letzte, wird die Lobby gelöscht; war er Host, wird ein anderer Host.
     */
    public function leaveLobby(string $token): array
    {
        $player = $this->playerByToken($token);
        $code = $player['lobby_code'];

        $this->db->beginTransaction();
        $this->db->prepare('DELETE FROM players WHERE id = ?')->execute([$player['id']]);
        $remaining = $this->playersOf($code);
        if ($remaining === []) {
            // Niemand mehr da → Lobby komplett löschen.
            $this->db->prepare('DELETE FROM lobbies WHERE code = ?')->execute([$code]);
        } else {
            $lobby = $this->findLobby($code);
            if ($lobby['host_id'] === $player['id']) {
                // Host ist weg: wer am längsten dabei ist, übernimmt.
                $this->db->prepare('UPDATE lobbies SET host_id = ? WHERE code = ?')
                    ->execute([$remaining[0]['id'], $code]);
            }
            // TODO (Issue „Spieler verlässt das Spiel“): Runde abbrechen, wenn der Wortgeber geht
            // oder weniger als MIN_PLAYERS übrig sind.
            $this->bumpVersion($code);
        }
        $this->db->commit();
        return ['ok' => true];
    }

    /**
     * Wird von jedem Spiel jede Sekunde aufgerufen (Polling).
     *
     * @param string $token        Geheimer Schlüssel des fragenden Spielers.
     * @param int    $sinceVersion Die Version, die das Spiel zuletzt bekommen hat.
     * @return array {"changed": false} oder {"changed": true, "state": {...}}
     */
    public function getState(string $token, int $sinceVersion): array
    {
        $player = $this->playerByToken($token);
        // Merken, wann der Spieler zuletzt gefragt hat – daraus wird "connected" berechnet.
        $this->db->prepare('UPDATE players SET last_seen = ? WHERE id = ?')->execute([time(), $player['id']]);

        $lobby = $this->findLobby($player['lobby_code']);
        // Nichts Neues seit der letzten Abfrage → nur "changed: false" schicken (spart Daten).
        if ((int) $lobby['version'] <= $sinceVersion) {
            return ['ok' => true, 'changed' => false];
        }
        return ['ok' => true, 'changed' => true, 'state' => $this->buildState($lobby, $player['id'])];
    }

    /**
     * Der Spielstand aus Sicht eines Spielers.
     * ACHTUNG: Hier wird entschieden, was ein Spieler zu sehen bekommt.
     * Das gesuchte Wort darf nur an den Wortgeber gehen, nie an die Ratenden!
     */
    private function buildState(array $lobby, string $viewerId): array
    {
        $now = time();
        // Aus jeder Datenbankzeile nur die öffentlichen Felder übernehmen.
        // Wichtig: Die Token der anderen Spieler werden NICHT mitgeschickt!
        $players = array_map(static fn(array $p): array => [
            'id' => $p['id'],
            'name' => $p['name'],
            'avatar' => (int) $p['avatar'],
            'score' => (int) $p['score'],
            // Verbunden = hat sich in den letzten TIMEOUT_SECONDS Sekunden gemeldet.
            'connected' => $now - (int) $p['last_seen'] <= self::TIMEOUT_SECONDS,
        ], $this->playersOf($lobby['code']));

        return [
            'version' => (int) $lobby['version'],
            // Damit das Spiel weiß, welcher Spieler in der Liste man selbst ist.
            'you' => $viewerId,
            'lobby' => [
                'code' => $lobby['code'],
                'host_id' => $lobby['host_id'],
                // "lobby" = Wartebereich; weitere Phasen (Spiel, Ergebnis) kommen später.
                'phase' => $lobby['phase'],
                'settings' => json_decode($lobby['settings'], true),
            ],
            'players' => $players,
            'round' => null, // kommt mit den Spielregeln (Meilenstein M2)
        ];
    }

    /** Gemeinsame Antwort von createLobby() und joinLobby(): alles, was das Spiel zum Mitspielen braucht. */
    private function enterResult(array $player): array
    {
        $lobby = $this->findLobby($player['lobby_code']);
        return [
            'ok' => true,
            'code' => $player['lobby_code'],
            'player_id' => $player['id'],
            // Das Token bekommt nur der Spieler selbst – damit meldet er sich bei allen weiteren Anfragen.
            'token' => $player['token'],
            'state' => $this->buildState($lobby, $player['id']),
        ];
    }

    /**
     * Prüft und bereinigt einen Spielernamen.
     *
     * @return string Der bereinigte Name (ohne Steuerzeichen und ohne Leerzeichen am Rand).
     * @throws ApiError wenn der Name leer oder länger als 16 Zeichen ist.
     */
    private function checkName(string $name): string
    {
        // Unsichtbare Steuerzeichen (Zeilenumbrüche, Tabs, …) entfernen, dann Leerzeichen am Rand abschneiden.
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '');
        if ($name === '') {
            throw new ApiError('Bitte gib einen Namen ein.');
        }
        // mb_strlen zählt Zeichen statt Bytes (wichtig bei Umlauten: "ä" ist 1 Zeichen, aber 2 Bytes).
        if (mb_strlen($name) > 16) {
            throw new ApiError('Der Name darf höchstens 16 Zeichen lang sein.');
        }
        return $name;
    }

    /** Ungültige Avatar-Nummern werden still auf 0 (erster Avatar) gesetzt. */
    private function checkAvatar(int $avatar): int
    {
        return ($avatar >= 0 && $avatar < self::AVATAR_COUNT) ? $avatar : 0;
    }

    /** Erzeugt einen zufälligen Lobby-Code, den es noch nicht gibt. */
    private function newLobbyCode(): string
    {
        do {
            $code = '';
            // CODE_LENGTH zufällige Zeichen aus CODE_CHARS aneinanderhängen.
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)];
            }
        } while ($this->findLobby($code) !== null); // Code schon vergeben → neuen würfeln.
        return $code;
    }

    /** $joinOrder: 1 für den Ersten in der Lobby, 2 für den Zweiten usw. – bestimmt die Reihenfolge. */
    private function newPlayer(string $code, string $name, int $avatar, int $joinOrder): array
    {
        return [
            // Öffentliche ID: 8 Zufallsbytes als Hex-Text (16 Zeichen), sehen alle Mitspieler.
            'id' => bin2hex(random_bytes(8)),
            // Geheimes Token: 24 Zufallsbytes (48 Zeichen), kennt nur der Spieler selbst.
            'token' => bin2hex(random_bytes(24)),
            'lobby_code' => $code,
            'name' => $name,
            'avatar' => $avatar,
            'join_order' => $joinOrder,
        ];
    }

    /** Speichert einen neuen Spieler (aus newPlayer()) in der Tabelle players. Punkte starten bei 0. */
    private function insertPlayer(array $p): void
    {
        $now = time();
        $this->db->prepare(
            'INSERT INTO players (id, token, lobby_code, name, avatar, score, join_order, joined_at, last_seen)
             VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)'
        )->execute([$p['id'], $p['token'], $p['lobby_code'], $p['name'], $p['avatar'], $p['join_order'], $now, $now]);
    }

    /** Sucht eine Lobby über ihren Code. Gibt null zurück, wenn es sie nicht gibt. */
    private function findLobby(string $code): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lobbies WHERE code = ?');
        $stmt->execute([$code]);
        // fetch() liefert false, wenn nichts gefunden wurde – "?: null" macht daraus null.
        return $stmt->fetch() ?: null;
    }

    /** Spieler einer Lobby in Beitrittsreihenfolge. */
    private function playersOf(string $code): array
    {
        $stmt = $this->db->prepare('SELECT * FROM players WHERE lobby_code = ? ORDER BY join_order');
        $stmt->execute([$code]);
        return $stmt->fetchAll();
    }

    /**
     * Sucht den Spieler zu einem Token.
     *
     * @throws ApiError (401) wenn das Token ungültig ist, z. B. weil der Spieler die Lobby verlassen hat.
     */
    private function playerByToken(string $token): array
    {
        $stmt = $this->db->prepare('SELECT * FROM players WHERE token = ?');
        $stmt->execute([$token]);
        return $stmt->fetch() ?: throw new ApiError('Du bist nicht (mehr) in dieser Lobby.', 401);
    }

    /** Erhöht die Version einer Lobby um 1 – alle Spiele holen sich dann beim nächsten Polling den neuen Stand. */
    private function bumpVersion(string $code): void
    {
        $this->db->prepare('UPDATE lobbies SET version = version + 1, updated_at = ? WHERE code = ?')
            ->execute([time(), $code]);
    }
}
