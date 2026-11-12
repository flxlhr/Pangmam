# Pangmam

Multiplayer-Hangman im Browser: Eine Person denkt sich ein Wort aus, bis zu sieben andere raten es gemeinsam. Wie bei skribbl.io erstellt man eine Lobby und schickt den Link an die anderen.

> Arbeitstitel. Uni-Softwareprojekt (Software Engineering, WS 2026/27). Abgabe zur letzten Übung am **28.01.2027**.

**Planung:** [Project-Board](https://github.com/users/flxlhr/projects/1) · [Meilensteine](https://github.com/flxlhr/Pangmam/milestones) · [Lastenheft v0.2](docs/lastenheft.md)

## So funktioniert es

```
Browser (Godot-Web-Export)  ──fragt jede Sekunde──▶  /api/  (PHP)  ──▶  Datenbank
   zeigt alles an                                      kennt das Wort, prüft jeden Zug
```

- **Spiel:** [Godot 4.7](https://godotengine.org) mit GDScript, als Web-Export im Browser (Ordner [`game/`](game/)).
- **Server:** kleine PHP-API mit Datenbank auf normalem Webspace (Ordner [`server/`](server/)). Kein eigener Server nötig.
- Weil Hangman rundenbasiert ist, reicht es, wenn jedes Spiel einmal pro Sekunde nachfragt, ob sich etwas geändert hat (*Polling*).
- Mehr dazu: [Technische Entscheidungen](docs/entscheidungen.md) · [API-Beschreibung](docs/api.md)

## Loslegen

### 1. Einmalig installieren

| Was | Wofür | Wie |
|---|---|---|
| **Godot 4.7** – Standard-Version, **nicht** „.NET“ | Spiel bauen | [godotengine.org/download](https://godotengine.org/download) |
| **PHP 8.1+** | API lokal starten | macOS: `brew install php` · Windows: [windows.php.net](https://windows.php.net/download/) (ZIP entpacken, Ordner zum PATH hinzufügen) |
| **Git** | Versionskontrolle | [git-scm.com](https://git-scm.com) oder GitHub Desktop |

### 2. Projekt holen

```bash
git clone https://github.com/flxlhr/Pangmam.git
cd Pangmam
```

### 3. API starten (im Projektordner, Terminal offen lassen)

```bash
php -S localhost:8000 -t server
```

Test: <http://localhost:8000/api/?action=ping> zeigt `{"ok":true,...}`. Die Datenbank (SQLite) legt sich beim ersten Aufruf selbst an.

### 4. Spiel starten

Godot öffnen → **Importieren** → `game/project.godot` wählen → **F5**.
Zum Testen mit mehreren Spielern: *Debug → Instanzen ausführen → 2* einstellen, dann öffnen sich zwei Fenster.

### Tests

```bash
php server/tests/run.php
```

## Ordner

```
game/          Godot-Projekt
  autoload/    Api (Server-Anfragen), Session (eigener Spieler + Polling), Screens (Bildschirmwechsel)
  screens/     start/, lobby/, game/, results/ – je eine Szene + Skript
  components/  wiederverwendbare Teile (Tastatur, Hangman-Grafik, Avatar-Rad …)
  assets/      Grafiken, Schriften, Sounds
server/        PHP-API
  api/         index.php – einziger Einstiegspunkt
  src/         Spiellogik und Datenbank
  tests/       Tests (php server/tests/run.php)
docs/          Lastenheft, Entscheidungen, API, Designs, Meeting-Notizen
.github/       Vorlagen für Issues/PRs, automatische Prüfungen (CI) und Deployment
```

## Mitmachen

Wie wir mit Branches, Pull Requests und Reviews arbeiten, steht in **[CONTRIBUTING.md](CONTRIBUTING.md)**. Kurz: Issue nehmen → Branch → Pull Request → ein Review → mergen.

## Lizenz

[MIT](LICENSE)
