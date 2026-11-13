<?php
/**
 * Vorlage für die Konfiguration auf dem Webspace.
 *
 * Auf dem Webspace als config.php neben die Ordner api/ und src/ legen
 * und mit den echten Zugangsdaten füllen. config.php NIE committen (steht in .gitignore).
 * Lokal braucht man keine config.php – dann wird SQLite benutzt.
 */

return [
    'db' => [
        'driver' => 'mysql',
        'host' => 'dbXXXXXXXXXX.hosting-data.io',
        'name' => 'dbsXXXXXXXX',
        'user' => 'dbuXXXXXXXX',
        'password' => 'HIER-PASSWORT',
    ],
    'debug' => false,
    // Spiel und API liegen auf derselben Adresse, daher kein CORS nötig.
    'allow_origin' => null,
];
