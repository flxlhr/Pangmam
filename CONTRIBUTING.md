# So arbeiten wir zusammen

## Unser Rhythmus

- **Jeden Donnerstag** treffen wir uns. Dort endet der alte Sprint und der neue beginnt.
- **Ein Sprint geht von Donnerstag bis Mittwoch.** Im Treffen schauen wir zurück (was ist fertig, was hängt?) und verteilen die Issues für die nächste Woche.
- Dazwischen gibt es keine festen Zeiten: Jede Person arbeitet, wann es passt. Abgestimmt wird auf Discord und in den Issues.
- Meilensteine enden immer an einem Donnerstag. Die Abgabe ist am 28.01.2027.
- Wer was verantwortet, steht in [`docs/team.md`](docs/team.md).

## Der Ablauf für jede Aufgabe

1. **Issue aussuchen** auf dem [Board](https://github.com/users/flxlhr/projects/1) (Spalte *Bereit*). Dich als *Assignee* eintragen und die Karte auf *In Arbeit* schieben.
2. **Branch anlegen** – immer von einem aktuellen `main`:
   ```bash
   git switch main
   git pull
   git switch -c feature/12-bildschirm-tastatur
   ```
   Schema: `feature/<issue-nr>-<kurz>`, `fix/<issue-nr>-<kurz>`, `docs/<kurz>`.
3. **Arbeiten und committen** – lieber oft und klein:
   ```bash
   git add <dateien>
   git commit -m "Bildschirm-Tastatur färbt Treffer grün"
   git push -u origin feature/12-bildschirm-tastatur
   ```
4. **Pull Request öffnen** (GitHub zeigt nach dem Push einen Button). In die Beschreibung `Closes #12` schreiben – dann schließt sich das Issue beim Mergen von selbst.
5. **Review:** Mindestens eine andere Person schaut drüber und gibt frei. Die automatischen Prüfungen (CI) müssen grün sein.
6. **Mergen** mit „Squash and merge“. Der Branch wird danach automatisch gelöscht.

`main` ist geschützt: Niemand pusht direkt darauf, alles läuft über Pull Requests.

## Commit-Nachrichten

- Deutsch, kurz, beschreibt **was sich ändert**: „Lobby zeigt Host an“, „Fehler beim Beitreten behoben“.
- Ein Commit = eine zusammenhängende Änderung.

## Reviews

- Ziel: Jede Person versteht den Code, der in `main` landet.
- Freundlich und konkret: „Was passiert hier, wenn die Lobby leer ist?“ statt „falsch“.
- Wer reviewt, probiert die Änderung kurz aus (Branch auschecken, starten).
- Reviews möglichst innerhalb von zwei Tagen.

## Code-Stil

- **Namen im Code auf Englisch** (`guess_letter`, `LobbyService`), **Kommentare und Texte im Spiel auf Deutsch**.
- **GDScript:** [offizieller Style Guide](https://docs.godotengine.org/de/4.x/tutorials/scripting/gdscript/gdscript_styleguide.html) – `snake_case`, Tabs, Typen angeben (`var count: int`). Die CI prüft mit `gdlint`/`gdformat`; lokal: `pip install "gdtoolkit==4.5.0"`, dann vor dem Commit `gdformat game/` und `gdlint game/`.
- **PHP:** 4 Leerzeichen, `declare(strict_types=1);`, eine Klasse pro Datei in `server/src/`.
- Neue API-Aktionen immer in [`docs/api.md`](docs/api.md) beschreiben und mit einem Test in `server/tests/run.php` absichern.

## Godot-Besonderheiten

- **Szenen (`.tscn`) lassen sich schlecht mergen.** An einer Szene arbeitet immer nur eine Person gleichzeitig – das Board zeigt, wer woran sitzt. Lieber Szenen in kleine Komponenten (`components/`) aufteilen.
- Die Ordner `.godot/` und `build/` werden nicht committet.
- `.uid`-Dateien neben Skripten **mit** committen.
- Alle nutzen **Godot 4.7** (Standard-Version, nicht .NET).

## Designs

Entwürfe (z. B. Figma-Exporte) kommen nach `docs/design/` mit Datum im Namen, z. B. `2026-11-20_spielbildschirm_v1.png`. So sieht man später die Entwicklung.

## KI-Werkzeuge

KI darf beim Lernen und Entwickeln helfen. Aber: **Alle müssen ihren Code erklären können.** Nichts ungeprüft übernehmen, und im Pull Request kurz erwähnen, wenn größere Teile mit KI entstanden sind.

## Fragen und Entscheidungen

- Kurze Abstimmung: Discord.
- Alles, was das Team entscheidet, kommt als Eintrag in [`docs/entscheidungen.md`](docs/entscheidungen.md) – sonst ist es in zwei Wochen vergessen.
- Offene Fragen als Issue mit dem Label `frage`.
