<?php

declare(strict_types=1);

/**
 * Fehler, den die Spieler sehen dürfen (falscher Code, Lobby voll, …).
 * Die Nachricht landet so wie sie ist im Spiel – also verständlich formulieren.
 *
 * Benutzung: throw new ApiError('Die Lobby ist voll.', 409);
 * api/index.php fängt den Fehler ab und schickt {"ok": false, "error": "..."} zurück.
 */
final class ApiError extends RuntimeException
{
    /**
     * @param string $message Text, der den Spielern angezeigt wird.
     * @param int    $status  HTTP-Statuscode, z. B. 400 (falsche Eingabe), 401 (nicht angemeldet),
     *                        404 (nicht gefunden), 409 (Konflikt, z. B. Name schon vergeben).
     */
    public function __construct(string $message, public readonly int $status = 400)
    {
        // Nachricht an die Eltern-Klasse weitergeben, damit getMessage() sie liefert.
        parent::__construct($message);
    }
}
