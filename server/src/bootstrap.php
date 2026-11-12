<?php
/**
 * Lädt Konfiguration und Klassen. Wird von api/index.php und den Tests eingebunden.
 *
 * Ohne config.php (also lokal) gelten die Standardwerte unten:
 * SQLite-Datei in data/, Debug-Meldungen an, Zugriff von jeder Adresse erlaubt.
 * Auf dem Webspace liegt eine config.php mit den MySQL-Zugangsdaten (siehe config.example.php).
 */

declare(strict_types=1);

$defaults = [
    'db' => ['driver' => 'sqlite', 'path' => __DIR__ . '/../data/pangmam.sqlite'],
    'debug' => true,
    'allow_origin' => '*',
];

$configFile = __DIR__ . '/../config.php';
$config = file_exists($configFile) ? array_replace($defaults, require $configFile) : $defaults;

require __DIR__ . '/ApiError.php';
require __DIR__ . '/Response.php';
require __DIR__ . '/Db.php';
require __DIR__ . '/LobbyService.php';
