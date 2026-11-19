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
| **PHP 8.1+** | API lokal starten | Siehe [PHP installieren](#php-installieren) direkt unter der Tabelle |
| **GitKraken** (empfohlen) | Git mit Oberfläche: Commits, Branches, Pull Requests | [gitkraken.com/download](https://www.gitkraken.com/download), mit GitHub anmelden. Kostenlos, weil unser Repo öffentlich ist |
| **Git** (optional) | Versionskontrolle im Terminal | [git-scm.com](https://git-scm.com) |

#### PHP installieren

Die API braucht PHP **8.1 oder neuer** mit den Erweiterungen **pdo_sqlite** (lokale Datenbank) und **mbstring** (Umlaute). XAMPP oder Apache braucht ihr nicht.

<details>
<summary><b>macOS</b></summary>

1. Falls noch nicht vorhanden, [Homebrew](https://brew.sh) installieren.
2. Im Terminal:
   ```bash
   brew install php
   ```
   pdo_sqlite und mbstring sind schon dabei.

</details>

<details>
<summary><b>Windows</b></summary>

1. Auf [windows.php.net/download](https://windows.php.net/download/) die neueste Version **„VS16 x64 Thread Safe“** als ZIP laden und nach `C:\php` entpacken.
   *Alternative:* `winget install PHP.PHP.8.3` im Terminal. Schritt 2 und 3 braucht ihr trotzdem; PHP liegt dann unter `%LOCALAPPDATA%\Microsoft\WinGet\Packages\…`, den Ordner zeigt `where php`.
2. Im PHP-Ordner die Datei `php.ini-development` kopieren und die Kopie in **`php.ini`** umbenennen.
3. `php.ini` mit einem Texteditor öffnen und bei diesen Zeilen das `;` am Anfang löschen:
   ```ini
   extension_dir = "ext"
   extension=mbstring
   extension=pdo_sqlite
   ```
4. Den PHP-Ordner zum **Path** hinzufügen: Startmenü → „Umgebungsvariablen bearbeiten“ → *Path* → *Bearbeiten* → *Neu* → `C:\php`. (Entfällt bei winget.)
5. **Neues** Terminal öffnen, damit der Path gilt.

</details>

<details>
<summary><b>Linux (Ubuntu/Debian)</b></summary>

```bash
sudo apt install php-cli php-sqlite3 php-mbstring
```

</details>

**Prüfen** (in einem neuen Terminal):

```bash
php -v
```

```bash
php -m
```

`php -v` muss 8.1 oder höher zeigen, in der Liste von `php -m` müssen `mbstring` und `pdo_sqlite` stehen.

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

| Problem | Ursache und Lösung |
|---|---|
| `php: command not found` / „Der Befehl php ist falsch geschrieben…“ | PHP ist nicht im Path, oder das Terminal war schon vor der Installation offen. Terminal neu öffnen, sonst Path prüfen. |
| `could not find driver` | pdo_sqlite ist nicht aktiviert. In der `php.ini` das `;` vor `extension=pdo_sqlite` löschen (siehe Windows-Schritt 3). |
| `Call to undefined function mb_…` | mbstring ist nicht aktiviert, gleiche Lösung mit `extension=mbstring`. |
| 404 / „Not Found“ statt `ok` | Server im falschen Ordner gestartet oder `-t server` vergessen. Der Befehl muss im Hauptordner `Pangmam` laufen. |
| `Address already in use` | Port 8000 ist belegt, meist von einem zweiten `php -S`. Das andere Terminal schließen; das Spiel erwartet die API fest auf Port 8000. |

Fehler der API stehen immer in dem Terminal, in dem `php -S` läuft.

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
