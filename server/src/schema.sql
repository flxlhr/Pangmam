-- Tabellen der Pangmam-API. Muss in SQLite (lokal) und MySQL (Webspace) funktionieren.
-- Nur "CREATE TABLE IF NOT EXISTS" verwenden. Kommentare nur als eigene Zeilen mit "--".
-- Achtung: Neue Spalten in einer bestehenden Tabelle brauchen ein ALTER TABLE auf dem Webspace.
-- Lokal reicht es, die Datei data/pangmam.sqlite zu löschen.

CREATE TABLE IF NOT EXISTS lobbies (
    code        VARCHAR(8)  NOT NULL PRIMARY KEY,
    host_id     VARCHAR(32) NOT NULL,
    phase       VARCHAR(16) NOT NULL,
    settings    TEXT        NOT NULL,
    round_data  TEXT        NULL,
    version     INTEGER     NOT NULL DEFAULT 1,
    updated_at  INTEGER     NOT NULL
);

CREATE TABLE IF NOT EXISTS players (
    id          VARCHAR(32) NOT NULL PRIMARY KEY,
    token       VARCHAR(64) NOT NULL,
    lobby_code  VARCHAR(8)  NOT NULL,
    name        VARCHAR(32) NOT NULL,
    avatar      INTEGER     NOT NULL DEFAULT 0,
    score       INTEGER     NOT NULL DEFAULT 0,
    joined_at   INTEGER     NOT NULL,
    last_seen   INTEGER     NOT NULL
);
