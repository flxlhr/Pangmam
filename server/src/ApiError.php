<?php

declare(strict_types=1);

/**
 * Fehler, den die Spieler sehen dürfen (falscher Code, Lobby voll, …).
 * Die Nachricht landet so wie sie ist im Spiel – also verständlich formulieren.
 */
final class ApiError extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 400)
    {
        parent::__construct($message);
    }
}
