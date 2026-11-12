# API

Die PHP-API in `server/` ist die Schnittstelle zwischen Spiel und Datenbank.
**Diese Datei ist der Vertrag zwischen Spiel und Server.** Wer eine Aktion ändert oder hinzufügt, aktualisiert sie hier.

## Grundsätzliches

- Adresse: lokal `http://localhost:8000/api/`, online `https://<domain>/api/` (eigene Domain, steht noch nicht fest)
- Jede Anfrage: `POST /api/?action=<aktion>` mit JSON im Body.
- Jede Antwort ist JSON mit `"ok": true` – oder `"ok": false` und `"error"`. Den Fehlertext kann das Spiel direkt anzeigen.
- Nach dem Beitreten weist man sich mit `token` aus (geheim, nur im Speicher des Spiels).

In Godot ruft man das so auf:

```gdscript
var res: Dictionary = await Api.call_action("join_lobby", {"code": "K7QX", "name": "Lara", "avatar": 2})
if res.ok:
	print(res.state)
```

## Aktionen

### `ping`
Prüft, ob die API läuft. → `{"ok": true, "message": "Pangmam-API läuft"}`

### `create_lobby`
`{"name": "Felix", "avatar": 0}` → erstellt eine Lobby, der Ersteller ist Host.

```json
{"ok": true, "code": "K7QX", "player_id": "f65b…", "token": "f4a4…", "state": { … }}
```

### `join_lobby`
`{"code": "K7QX", "name": "Lara", "avatar": 3}` → gleiche Antwort wie `create_lobby`.
Fehler: Lobby gibt es nicht, Lobby voll, Name vergeben, Spiel läuft schon.

### `leave_lobby`
`{"token": "…"}` → `{"ok": true}`. Ist der Host weg, übernimmt der nächste Spieler. Die letzte Person löscht die Lobby.

### `state` (Polling, jede Sekunde)
`{"token": "…", "since": 4}` → hat sich seit Version 4 nichts geändert: `{"ok": true, "changed": false}`, sonst:

```json
{"ok": true, "changed": true, "state": { … }}
```

### Geplant (siehe Issues)
`update_settings`, `start_game`, `set_word`, `guess_letter`, `propose_word`, `vote`

## Der Spielstand (`state`)

```json
{
  "version": 5,
  "you": "f65b9a68dd9482ca",
  "lobby": {
    "code": "K7QX",
    "host_id": "f65b9a68dd9482ca",
    "phase": "lobby",
    "settings": {"max_players": 8, "rounds": 1, "max_mistakes": 10}
  },
  "players": [
    {"id": "f65b9a68dd9482ca", "name": "Felix", "avatar": 0, "score": 0, "connected": true}
  ],
  "round": null
}
```

- `version` steigt bei jeder Änderung an der Lobby.
- `phase`: `lobby` → (geplant) `choosing_word` → `guessing` → `round_end` → `results`
- `connected: false`, wenn ein Spieler länger als 10 Sekunden nicht nachgefragt hat.

**Vorschlag für `round`** (wird mit den Spielregeln umgesetzt, darf sich noch ändern):

```json
{
  "number": 1,
  "word_giver_id": "…",
  "pattern": "B_B__O___ ___",
  "word": "BIBLIOTHEK …",
  "guessed": {"correct": ["B", "O"], "wrong": ["X", "Q"]},
  "mistakes": 2,
  "turn_player_id": "…",
  "out_player_ids": [],
  "vote": null,
  "winner": null
}
```

`word` bekommt **nur der Wortgeber** (NF-09). Leerzeichen in `pattern` trennen Wörter.
