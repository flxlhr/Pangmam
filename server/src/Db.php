<?php

declare(strict_types=1);

/**
 * Datenbankverbindung: lokal SQLite (eine Datei, nichts zu installieren),
 * auf dem Webspace MySQL. Dank PDO funktionieren dieselben SQL-Befehle in beiden.
 */
final class Db
{
    public static function connect(array $config): PDO
    {
        $db = $config['db'];
        if ($db['driver'] === 'sqlite') {
            $pdo = new PDO('sqlite:' . $db['path']);
        } else {
            $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['password']);
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Tabellen nur einmal anlegen; die Markierungsdatei spart die Prüfung bei jeder Anfrage.
        $marker = __DIR__ . '/../data/.schema-' . self::schemaVersion();
        if (!file_exists($marker)) {
            self::createSchema($pdo);
            @touch($marker);
        }
        return $pdo;
    }

    /** Legt alle Tabellen an, falls sie fehlen (auch für Tests mit sqlite::memory:). */
    public static function createSchema(PDO $pdo): void
    {
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $sql = preg_replace('/^\s*--.*$/m', '', $sql); // Kommentarzeilen entfernen
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            $pdo->exec($statement);
        }
    }

    /** Ändert sich, sobald jemand schema.sql bearbeitet – dann wird neu angelegt. */
    private static function schemaVersion(): string
    {
        return substr(md5_file(__DIR__ . '/schema.sql'), 0, 8);
    }
}
