<?php
/**
 * Lädt Konfiguration und Klassen. Wird von api/index.php und den Tests eingebunden.
 *
 * Ohne config.php (also lokal) gelten die Standardwerte unten:
 * SQLite-Datei in data/, Debug-Meldungen an, Zugriff von jeder Adresse erlaubt.
 * Auf dem Webspace liegt eine config.php mit den MySQL-Zugangsdaten (siehe config.example.php).
 */

declare(strict_types=1);

// Standard-Konfiguration für die lokale Entwicklung.
$defaults = [
    // Datenbank: eine einfache SQLite-Datei im Ordner data/.
    'db' => ['driver' => 'sqlite', 'path' => __DIR__ . '/../data/pangmam.sqlite'],
    // Debug an: Bei Fehlern wird die genaue Fehlermeldung angezeigt.
    'debug' => true,
    // CORS: Jede Adresse darf die API aufrufen ("*").
    'allow_origin' => '*',
];

// Gibt es eine config.php (auf dem Webspace), überschreiben deren Werte die Standardwerte.
// "require $configFile" liefert das Array zurück, das in config.php mit "return [...]" steht.
$configFile = __DIR__ . '/../config.php';
$config = file_exists($configFile) ? array_replace($defaults, require $configFile) : $defaults;

// Alle Klassen der API laden (ohne Autoloader, weil es nur wenige Dateien sind).
require __DIR__ . '/ApiError.php';
require __DIR__ . '/Response.php';
require __DIR__ . '/Db.php';
require __DIR__ . '/LobbyService.php';
