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
    public const DEFAULT_SETTINGS = [
        'max_players' => 8,
        'rounds' => 1,          // Durchgänge: jeder ist einmal Wortgeber
        'max_mistakes' => 10,   // Hangman-Teile, Gestell ist schon vorgezeichnet
    ];
    public const MIN_PLAYERS = 2;
    public const AVATAR_COUNT = 12;
    /** Wer so viele Sekunden nicht mehr nachgefragt hat, gilt als „weg“. */
    public const TIMEOUT_SECONDS = 10;
    /** Ohne 0/O und 1/I, damit man Codes gut vorlesen kann. */
    private const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 4;

    public function __construct(private readonly PDO $db)
    {
    }

    public function createLobby(string $name, int $avatar): array
    {
        $name = $this->checkName($name);
        $code = $this->newLobbyCode();
        $player = $this->newPlayer($code, $name, $this->checkAvatar($avatar));

        $this->db->beginTransaction();
        $this->db->prepare(
            'INSERT INTO lobbies (code, host_id, phase, settings, version, updated_at) VALUES (?, ?, ?, ?, 1, ?)'
        )->execute([$code, $player['id'], 'lobby', json_encode(self::DEFAULT_SETTINGS), time()]);
        $this->insertPlayer($player);
        $this->db->commit();

        return $this->enterResult($player);
    }

    public function joinLobby(string $code, string $name, int $avatar): array
    {
        $code = strtoupper(trim($code));
        $name = $this->checkName($name);

        $this->db->beginTransaction();
        try {
            $lobby = $this->findLobby($code) ?? throw new ApiError('Diese Lobby gibt es nicht (mehr).', 404);
            if ($lobby['phase'] !== 'lobby') {
                throw new ApiError('Das Spiel läuft schon. Warte, bis es vorbei ist.', 409);
            }
            $players = $this->playersOf($code);
            $settings = json_decode($lobby['settings'], true);
            if (count($players) >= $settings['max_players']) {
                throw new ApiError('Die Lobby ist voll.', 409);
            }
            foreach ($players as $other) {
                if (mb_strtolower($other['name']) === mb_strtolower($name)) {
                    throw new ApiError('Der Name ist in dieser Lobby schon vergeben.', 409);
                }
            }
            $player = $this->newPlayer($code, $name, $this->checkAvatar($avatar));
            $this->insertPlayer($player);
            $this->bumpVersion($code);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $this->enterResult($player);
    }

    public function leaveLobby(string $token): array
    {
        $player = $this->playerByToken($token);
        $code = $player['lobby_code'];

        $this->db->beginTransaction();
        $this->db->prepare('DELETE FROM players WHERE id = ?')->execute([$player['id']]);
        $remaining = $this->playersOf($code);
        if ($remaining === []) {
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

    /** Wird von jedem Spiel jede Sekunde aufgerufen (Polling). */
    public function getState(string $token, int $sinceVersion): array
    {
        $player = $this->playerByToken($token);
        $this->db->prepare('UPDATE players SET last_seen = ? WHERE id = ?')->execute([time(), $player['id']]);

        $lobby = $this->findLobby($player['lobby_code']);
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
        $players = array_map(static fn(array $p): array => [
            'id' => $p['id'],
            'name' => $p['name'],
            'avatar' => (int) $p['avatar'],
            'score' => (int) $p['score'],
            'connected' => $now - (int) $p['last_seen'] <= self::TIMEOUT_SECONDS,
        ], $this->playersOf($lobby['code']));

        return [
            'version' => (int) $lobby['version'],
            'you' => $viewerId,
            'lobby' => [
                'code' => $lobby['code'],
                'host_id' => $lobby['host_id'],
                'phase' => $lobby['phase'],
                'settings' => json_decode($lobby['settings'], true),
            ],
            'players' => $players,
            'round' => null, // kommt mit den Spielregeln (Meilenstein M2)
        ];
    }

    private function enterResult(array $player): array
    {
        $lobby = $this->findLobby($player['lobby_code']);
        return [
            'ok' => true,
            'code' => $player['lobby_code'],
            'player_id' => $player['id'],
            'token' => $player['token'],
            'state' => $this->buildState($lobby, $player['id']),
        ];
    }

    private function checkName(string $name): string
    {
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '');
        if ($name === '') {
            throw new ApiError('Bitte gib einen Namen ein.');
        }
        if (mb_strlen($name) > 16) {
            throw new ApiError('Der Name darf höchstens 16 Zeichen lang sein.');
        }
        return $name;
    }

    private function checkAvatar(int $avatar): int
    {
        return ($avatar >= 0 && $avatar < self::AVATAR_COUNT) ? $avatar : 0;
    }

    private function newLobbyCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)];
            }
        } while ($this->findLobby($code) !== null);
        return $code;
    }

    private function newPlayer(string $code, string $name, int $avatar): array
    {
        return [
            'id' => bin2hex(random_bytes(8)),
            'token' => bin2hex(random_bytes(24)),
            'lobby_code' => $code,
            'name' => $name,
            'avatar' => $avatar,
        ];
    }

    private function insertPlayer(array $p): void
    {
        $now = time();
        $this->db->prepare(
            'INSERT INTO players (id, token, lobby_code, name, avatar, score, joined_at, last_seen)
             VALUES (?, ?, ?, ?, ?, 0, ?, ?)'
        )->execute([$p['id'], $p['token'], $p['lobby_code'], $p['name'], $p['avatar'], $now, $now]);
    }

    private function findLobby(string $code): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lobbies WHERE code = ?');
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    /** Spieler einer Lobby in Beitrittsreihenfolge. */
    private function playersOf(string $code): array
    {
        $stmt = $this->db->prepare('SELECT * FROM players WHERE lobby_code = ? ORDER BY joined_at, id');
        $stmt->execute([$code]);
        return $stmt->fetchAll();
    }

    private function playerByToken(string $token): array
    {
        $stmt = $this->db->prepare('SELECT * FROM players WHERE token = ?');
        $stmt->execute([$token]);
        return $stmt->fetch() ?: throw new ApiError('Du bist nicht (mehr) in dieser Lobby.', 401);
    }

    private function bumpVersion(string $code): void
    {
        $this->db->prepare('UPDATE lobbies SET version = version + 1, updated_at = ? WHERE code = ?')
            ->execute([time(), $code]);
    }
}
