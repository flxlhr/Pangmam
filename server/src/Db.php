<?php

declare(strict_types=1);

/**
 * Datenbankverbindung: lokal SQLite (eine Datei, nichts zu installieren),
 * auf dem Webspace MySQL. Dank PDO funktionieren dieselben SQL-Befehle in beiden.
 */
final class Db
{
    /**
     * Öffnet die Datenbank laut Konfiguration und legt bei Bedarf die Tabellen an.
     *
     * @param array $config Die Konfiguration aus bootstrap.php (Schlüssel 'db').
     * @return PDO Die fertige Verbindung, mit der der LobbyService arbeitet.
     */
    public static function connect(array $config): PDO
    {
        $db = $config['db'];
        if ($db['driver'] === 'sqlite') {
            // SQLite: nur der Pfad zur Datei wird gebraucht; die Datei entsteht automatisch.
            $pdo = new PDO('sqlite:' . $db['path']);
        } else {
            // MySQL: Server, Datenbankname, Benutzer und Passwort aus config.php.
            // utf8mb4 = vollständiges UTF-8 (auch Emojis).
            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['password']);
        }
        // Bei SQL-Fehlern eine Exception werfen statt still false zurückzugeben.
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Ergebniszeilen als Array mit Spaltennamen liefern, z. B. $row['name'].
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Tabellen nur einmal anlegen; die Markierungsdatei spart die Prüfung bei jeder Anfrage.
        $marker = __DIR__ . '/../data/.schema-' . self::schemaVersion();
        if (!file_exists($marker)) {
            self::createSchema($pdo);
            // "@" unterdrückt die Warnung, falls der Ordner nicht beschreibbar ist.
            @touch($marker);
        }
        return $pdo;
    }

    /** Legt alle Tabellen an, falls sie fehlen (auch für Tests mit sqlite::memory:). */
    public static function createSchema(PDO $pdo): void
    {
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $sql = preg_replace('/^\s*--.*$/m', '', $sql); // Kommentarzeilen entfernen
        // Datei an jedem ";" in einzelne Befehle zerlegen, Leerzeichen abschneiden,
        // leere Teile wegwerfen und jeden Befehl einzeln ausführen.
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            $pdo->exec($statement);
        }
    }

    /** Ändert sich, sobald jemand schema.sql bearbeitet – dann wird neu angelegt. */
    private static function schemaVersion(): string
    {
        // Prüfsumme (MD5) der Datei, davon die ersten 8 Zeichen, z. B. "9619a117".
        return substr(md5_file(__DIR__ . '/schema.sql'), 0, 8);
    }
}
