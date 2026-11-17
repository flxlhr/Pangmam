-- Tabellen der Pangmam-API. Muss in SQLite (lokal) und MySQL (Webspace) funktionieren.
-- Nur "CREATE TABLE IF NOT EXISTS" verwenden. Kommentare nur als eigene Zeilen mit "--".
-- Achtung: Neue Spalten in einer bestehenden Tabelle brauchen ein ALTER TABLE auf dem Webspace.
-- Lokal reicht es, die Datei data/pangmam.sqlite zu löschen.

-- Eine Zeile pro Lobby (Spielraum).
--   code        4-stelliger Lobby-Code zum Beitreten, z. B. K7QX (eindeutig)
--   host_id     ID des Spielers, der die Lobby leitet (verweist auf players.id)
--   phase       aktueller Abschnitt, z. B. "lobby" (Wartebereich)
--   settings    Spielregeln als JSON-Text (max_players, rounds, max_mistakes)
--   round_data  Daten der laufenden Runde als JSON-Text, noch leer (kommt mit Meilenstein M2)
--   version     wird bei jeder Änderung erhöht, damit die Spiele Neues erkennen
--   updated_at  Zeitpunkt der letzten Änderung (Unix-Zeit in Sekunden)
CREATE TABLE IF NOT EXISTS lobbies (
    code        VARCHAR(8)  NOT NULL PRIMARY KEY,
    host_id     VARCHAR(32) NOT NULL,
    phase       VARCHAR(16) NOT NULL,
    settings    TEXT        NOT NULL,
    round_data  TEXT        NULL,
    version     INTEGER     NOT NULL DEFAULT 1,
    updated_at  INTEGER     NOT NULL
);

-- Eine Zeile pro Spieler.
--   id          öffentliche Spieler-ID, sehen alle in der Lobby
--   token       geheimer Schlüssel, mit dem sich der Spieler bei jeder Anfrage ausweist
--   lobby_code  in welcher Lobby der Spieler ist (verweist auf lobbies.code)
--   name        angezeigter Name (höchstens 16 Zeichen)
--   avatar      Nummer des gewählten Avatars (0 bis 11)
--   score       Punktestand
--   join_order  Reihenfolge des Beitritts (1 = Erster), bestimmt z. B. den nächsten Host
--   joined_at   Zeitpunkt des Beitritts (Unix-Zeit)
--   last_seen   letzte Anfrage des Spielers (Unix-Zeit), daraus wird "verbunden" berechnet
CREATE TABLE IF NOT EXISTS players (
    id          VARCHAR(32) NOT NULL PRIMARY KEY,
    token       VARCHAR(64) NOT NULL,
    lobby_code  VARCHAR(8)  NOT NULL,
    name        VARCHAR(32) NOT NULL,
    avatar      INTEGER     NOT NULL DEFAULT 0,
    score       INTEGER     NOT NULL DEFAULT 0,
    join_order  INTEGER     NOT NULL,
    joined_at   INTEGER     NOT NULL,
    last_seen   INTEGER     NOT NULL
);
