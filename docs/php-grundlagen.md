# PHP-Grundlagen für Pangmam

Alles, was du brauchst, um an unserem Server (`server/`) mitzuarbeiten. Die Beispiele stammen so weit wie möglich aus unserem eigenen Code.
Für GDScript gibt es ein eigenes Dokument: [gdscript-grundlagen.md](gdscript-grundlagen.md).

**Videos zum Einstieg**

Wer lieber erst zuschaut und dann hier nachliest:

| Sprache | Video | Kanal | Länge | Hinweis |
|---|---|---|---|---|
| 🇩🇪 Deutsch | [Lerne PHP in 10 Stunden – Kompletter Kurs](https://www.youtube.com/watch?v=NH8Nx-ZWWAA) | Mit Mario | ca. 10 h | Aktuell (2025), von null an. Einzelne Kapitel über die Zeitmarken anspringen |
| 🇬🇧 Englisch | [PHP Full Course](https://www.youtube.com/watch?v=zZ6vybT1HQs) | Bro Code | ca. 4 h | Kompakter Überblick über die Grundlagen bis zur Datenbank |
| 🇬🇧 Englisch | [Learn PHP The Right Way](https://www.youtube.com/playlist?list=PLr3d3QYzkw2xabQRUpcZ_IBk9W50M9pe-) | Program With Gio | Playlist, viele kurze Folgen | Modernes PHP 8 mit Typen, `strict_types`, Klassen und PDO. Passt am besten zu unserem Stil |

Viele Kurse arbeiten mit XAMPP und mischen PHP in HTML-Seiten. Das brauchen wir nicht: Bei uns reicht `php -S` (siehe [Werkzeuge](#2-werkzeuge-und-befehle)), und PHP gibt nur JSON aus. Die Sprachgrundlagen sind aber dieselben.

**Inhalt**

1. [Was PHP bei uns macht](#1-was-php-bei-uns-macht)
2. [Werkzeuge und Befehle](#2-werkzeuge-und-befehle)
3. [Aufbau einer PHP-Datei](#3-aufbau-einer-php-datei)
4. [Variablen und Typen](#4-variablen-und-typen)
5. [Strings](#5-strings)
6. [Arrays](#6-arrays)
7. [Operatoren](#7-operatoren)
8. [Kontrollstrukturen](#8-kontrollstrukturen)
9. [Funktionen](#9-funktionen)
10. [Klassen und Objekte](#10-klassen-und-objekte)
11. [Fehler und Exceptions](#11-fehler-und-exceptions)
12. [Dateien einbinden, Superglobals, magische Konstanten](#12-dateien-einbinden-superglobals-magische-konstanten)
13. [Datenbank mit PDO](#13-datenbank-mit-pdo)
14. [JSON](#14-json)
15. [Wichtige eingebaute Funktionen](#15-wichtige-eingebaute-funktionen)
16. [Präfixe, Suffixe und Sonderzeichen auf einen Blick](#16-präfixe-suffixe-und-sonderzeichen-auf-einen-blick)
17. [Typische Stolperfallen](#17-typische-stolperfallen)
18. [Sicherheit](#18-sicherheit)
19. [Rezept: eine neue API-Aktion hinzufügen](#19-rezept-eine-neue-api-aktion-hinzufügen)
20. [Unsere Regeln (Kurzfassung)](#20-unsere-regeln-kurzfassung)
21. [Zum Weiterlesen](#21-zum-weiterlesen)

---

## 1. Was PHP bei uns macht

PHP ist eine Skriptsprache, die **auf dem Server** läuft. Bei uns ist das so:

```
Godot-Spiel ──POST /api/?action=join_lobby──▶ api/index.php ──▶ LobbyService ──▶ Datenbank
            ◀──────────── JSON-Antwort ───────────┘
```

- Für **jede Anfrage** startet PHP das Skript `api/index.php` neu, arbeitet es von oben nach unten ab und vergisst danach alles. Variablen überleben **nicht** von einer Anfrage zur nächsten. Was dauerhaft bleiben soll, muss in die Datenbank.
- PHP erzeugt bei uns kein HTML, sondern nur **JSON**. Die Anzeige macht komplett Godot.
- **Der Server ist die Wahrheit.** Das Spiel darf nur anzeigen und Wünsche schicken. Ob ein Zug erlaubt ist, entscheidet PHP. Sonst könnte jemand im Browser schummeln.

Wir benutzen **PHP 8.1 oder neuer** (der Webspace hat mindestens 8.1, die CI testet mit 8.2). Funktionen, die es erst ab PHP 8.2 oder 8.3 gibt, bitte **nicht** benutzen (z. B. `readonly class`).

## 2. Werkzeuge und Befehle

| Befehl | Was er macht |
|---|---|
| `php -S localhost:8000 -t server` | Startet einen kleinen Webserver für die API. Im Projektordner ausführen, Terminal offen lassen. |
| `php server/tests/run.php` | Führt alle Tests aus. |
| `php -l datei.php` | Prüft nur die Syntax („lint“), ohne die Datei auszuführen. |
| `php -v` | Zeigt die installierte Version. |
| `php -r 'echo strlen("Hallo");'` | Führt eine einzelne Zeile PHP aus. Gut zum schnellen Ausprobieren. |
| `php -a` | Interaktive Konsole, um Dinge auszuprobieren. |

API im Browser testen: <http://localhost:8000/api/?action=ping>
Andere Aktionen brauchen einen POST mit JSON, z. B. im Terminal:

```bash
curl -X POST "http://localhost:8000/api/?action=create_lobby" -H "Content-Type: application/json" -d '{"name":"Felix","avatar":0}'
```

**Editor:** VS Code mit der Erweiterung *PHP Intelephense* zeigt Fehler, Typen und Autovervollständigung.

**Datenbank zurücksetzen (lokal):** Datei `server/data/pangmam.sqlite` löschen. Sie wird beim nächsten Aufruf neu angelegt.

## 3. Aufbau einer PHP-Datei

```php
<?php

declare(strict_types=1);

/**
 * Was die Klasse macht (Doc-Kommentar).
 */
final class Response
{
    // ...
}
```

- **`<?php`** muss ganz am Anfang stehen, nichts davor, auch kein Leerzeichen und keine Leerzeile. Sonst schickt PHP diese Zeichen an das Spiel, und die JSON-Antwort ist kaputt.
- Ein **schließendes `?>` lassen wir weg.** Es ist in reinen PHP-Dateien unnötig und verursacht genau das Problem von oben, wenn danach noch eine Leerzeile steht.
- **`declare(strict_types=1);`** steht in jeder Datei direkt nach `<?php`. Damit prüft PHP Typen streng: Eine Funktion, die `int` erwartet, bekommt dann nicht still `"5"` umgewandelt, sondern es gibt einen Fehler. Das findet viele Bugs sofort.
- **Jede Anweisung endet mit `;`.** Ein fehlendes Semikolon ist der häufigste Syntaxfehler.
- **Kommentare:**
  ```php
  // einzeilig
  # auch einzeilig (benutzen wir nicht)
  /* mehrzeilig */
  /** Doc-Kommentar: steht vor Klassen, Funktionen und Konstanten und beschreibt sie. */
  ```
- **Groß-/Kleinschreibung:** Variablen unterscheiden sie (`$name` ≠ `$Name`). Funktions- und Klassennamen nicht (`strlen` = `STRLEN`), schreib sie trotzdem immer gleich.

## 4. Variablen und Typen

### Variablen

```php
$name = 'Felix';      // Jede Variable beginnt mit $
$avatar = 3;
$name = 'Lara';       // Überschreiben geht jederzeit
```

- Variablen werden nicht vorher angelegt („deklariert“), sondern entstehen beim ersten Zuweisen.
- Das `$` gehört **immer** dazu, auch beim Lesen: `echo $name;`
- Benutzt man eine Variable, die nie gesetzt wurde, gibt es eine Warnung und den Wert `null`.

### Die Grundtypen

| Typ | Beispiel | Bemerkung |
|---|---|---|
| `int` | `42`, `-1`, `0` | Ganze Zahl |
| `float` | `3.14`, `1.0` | Kommazahl (Punkt statt Komma!) |
| `string` | `'Hallo'`, `"Hallo"` | Text |
| `bool` | `true`, `false` | Wahr/falsch |
| `array` | `[1, 2, 3]`, `['name' => 'Felix']` | Liste **und** Wörterbuch zugleich, siehe [Arrays](#6-arrays) |
| `null` | `null` | „Kein Wert“ |
| Objekte | `new LobbyService($pdo)` | Instanz einer Klasse |
| `callable` | `fn() => 1`, `'strlen'` | Etwas, das man aufrufen kann |

### Typangaben (Type Hints)

Wir schreiben bei Parametern, Rückgabewerten und Eigenschaften **immer** den Typ dazu:

```php
public function joinLobby(string $code, string $name, int $avatar): array
```

| Schreibweise | Bedeutung |
|---|---|
| `string $code` | Parameter muss ein String sein |
| `: array` | Funktion gibt ein Array zurück |
| `: void` | Funktion gibt nichts zurück |
| `?string` | String **oder** `null` („nullable“, das `?` ist ein Präfix) |
| `int\|string` | Int oder String (Union Type) |
| `mixed` | Beliebiger Typ (möglichst vermeiden) |
| `: never` | Funktion kehrt nie zurück (wirft immer einen Fehler oder beendet das Skript) |
| `: static` / `: self` | Gibt ein Objekt der eigenen Klasse zurück |

### Typen umwandeln (Casts)

```php
(int) '42'      // 42
(int) '4abc'    // 4   – liest so weit wie möglich
(int) 'abc'     // 0
(string) 5      // '5'
(bool) ''       // false
(array) 'x'     // ['x']
```

In `api/index.php` steht deshalb `(int) ($input['avatar'] ?? 0)`: Was aus JSON kommt, ist nicht garantiert eine Zahl, also wandeln wir es um, bevor es an eine Funktion mit `int`-Parameter geht.

### Typ prüfen

```php
is_int($x)   is_string($x)   is_array($x)   is_bool($x)   is_null($x)   is_numeric('12')
gettype($x)        // 'integer', 'string', ...
get_debug_type($x) // 'int', 'string', 'LobbyService', ... (besser lesbar)
```

### Was als „falsch“ gilt (falsy)

In `if`-Bedingungen gelten diese Werte als `false`: `false`, `0`, `0.0`, `''`, `'0'` (!), `[]` und `null`. Alles andere ist `true`.
Achtung: Der String `'0'` ist falsy, der String `'0.0'` dagegen nicht.

### Konstanten

```php
const MAX = 8;                      // auf oberster Ebene oder in Klassen
public const MIN_PLAYERS = 2;       // Klassenkonstante, Zugriff: self::MIN_PLAYERS oder LobbyService::MIN_PLAYERS
```

Konstanten haben **kein** `$` und werden in `GROSSBUCHSTABEN_MIT_UNTERSTRICH` geschrieben.

## 5. Strings

### Einfache vs. doppelte Anführungszeichen

Das ist **der** wichtigste Unterschied:

```php
$name = 'Lara';
echo 'Hallo $name\n';   // Ausgabe: Hallo $name\n        – nichts wird ersetzt
echo "Hallo $name\n";   // Ausgabe: Hallo Lara + Zeilenumbruch
```

- `'...'`: Text genau so, wie er dasteht. Nur `\'` und `\\` sind Sonderzeichen.
- `"..."`: Variablen werden eingesetzt („Interpolation“) und `\n` (Zeilenumbruch), `\t` (Tab), `\"`, `\$` funktionieren.
- Bei Array-Einträgen oder Ausdrücken in `"..."` geschweifte Klammern benutzen:
  ```php
  "Spieler {$player['name']} ist da"
  "Unbekannte Aktion „{$action}“."
  "mysql:host={$db['host']};dbname={$db['name']}"
  ```

Faustregel bei uns: **`'...'` als Standard**, `"..."` nur, wenn etwas eingesetzt werden soll.

### Strings verbinden

Mit dem **Punkt** `.`, nicht mit `+`:

```php
$url = 'sqlite:' . $db['path'];
$text .= '!';             // anhängen, kurz für $text = $text . '!';
```

`'1' + '2'` ergibt in PHP die **Zahl 3**, nicht `'12'`.

### Zeichen und Umlaute: die `mb_`-Funktionen

PHP-Strings sind intern Bytes. Ein Umlaut wie „ä“ besteht in UTF-8 aus **2 Bytes**. Die normalen Funktionen zählen Bytes, die `mb_`-Funktionen („multibyte“) zählen echte Zeichen:

```php
strlen('Bär')         // 4  – Bytes
mb_strlen('Bär')      // 3  – Zeichen ✔
strtoupper('bär')     // 'BäR' – ä bleibt klein!
mb_strtoupper('bär')  // 'BÄR' ✔
```

**Regel:** Bei allem, was Spieler eingeben (Namen, Wörter, Buchstaben), immer die `mb_`-Variante nehmen. Für Hangman ist das besonders wichtig: „Ä“, „Ö“, „Ü“ und „ß“ müssen richtig behandelt werden.

### Nützliche String-Funktionen

| Funktion | Beispiel | Ergebnis |
|---|---|---|
| `trim($s)` | `trim('  Felix ')` | `'Felix'` |
| `mb_strtolower` / `mb_strtoupper` | `mb_strtoupper('k7qx')` | `'K7QX'` |
| `mb_substr($s, $start, $länge)` | `mb_substr('Pangmam', 0, 4)` | `'Pang'` |
| `str_contains($s, $teil)` | `str_contains('Hangman', 'man')` | `true` |
| `str_starts_with` / `str_ends_with` | `str_starts_with('feature/12', 'feature/')` | `true` |
| `str_replace($alt, $neu, $s)` | `str_replace('a', '_', 'Bahn')` | `'B_hn'` |
| `explode($trenner, $s)` | `explode(';', 'a;b')` | `['a', 'b']` |
| `implode($trenner, $array)` | `implode(', ', ['a', 'b'])` | `'a, b'` |
| `str_repeat($s, $n)` | `str_repeat('_', 5)` | `'_____'` |
| `sprintf($format, ...)` | `sprintf('%d Spieler', 3)` | `'3 Spieler'` |
| `mb_str_split($s)` | `mb_str_split('Bär')` | `['B', 'ä', 'r']` |
| `preg_match` / `preg_replace` | reguläre Ausdrücke | siehe [php.net/pcre](https://www.php.net/manual/de/book.pcre.php) |

Bei regulären Ausdrücken mit Umlauten das Flag `u` anhängen: `preg_replace('/[\x00-\x1F]/u', '', $name)`.

### Heredoc (mehrzeilige Strings)

```php
$sql = <<<SQL
    SELECT *
    FROM players
    WHERE lobby_code = ?
    SQL;
```

Wie `"..."`, Variablen werden also eingesetzt. Mit `<<<'SQL'` (Nowdoc) wie `'...'`, ohne Ersetzung.

## 6. Arrays

In PHP gibt es nur **einen** Typ für Listen und Wörterbücher: `array`.

### Liste (Index-Array)

```php
$letters = ['A', 'B', 'C'];
echo $letters[0];          // 'A' – Zählung beginnt bei 0
$letters[] = 'D';          // hinten anhängen
count($letters);           // 4
```

### Wörterbuch (assoziatives Array)

```php
$player = [
    'name' => 'Felix',     // Schlüssel => Wert
    'avatar' => 3,
];
echo $player['name'];      // 'Felix'
$player['score'] = 10;     // neuen Eintrag hinzufügen
unset($player['score']);   // Eintrag löschen
```

Das kennst du aus unserem Code: `DEFAULT_SETTINGS`, die Antworten der API und jede Zeile aus der Datenbank sind solche Arrays. Aus PHP-Arrays wird mit `json_encode` direkt JSON, und in Godot kommt ein `Dictionary` an.

- Die Reihenfolge bleibt immer so, wie man die Einträge hinzugefügt hat.
- **Am letzten Eintrag ein Komma stehen lassen** (`'avatar' => 3,`). Das ist erlaubt und macht spätere Diffs kleiner.
- Arrays werden **kopiert**, wenn man sie einer anderen Variable zuweist oder an eine Funktion übergibt. Änderungen an der Kopie ändern das Original nicht. (Objekte verhalten sich anders, siehe [Klassen](#10-klassen-und-objekte).)

### Prüfen, ob ein Schlüssel existiert

```php
isset($player['name'])            // true, wenn vorhanden UND nicht null
array_key_exists('name', $player) // true, wenn vorhanden (auch wenn null)
$player['name'] ?? 'Unbekannt'    // Wert oder Standardwert – das benutzen wir am häufigsten
in_array('A', $letters, true)     // Ist der WERT 'A' in der Liste? (true = strenger Vergleich)
```

Liest man einen Schlüssel, den es nicht gibt (`$player['xyz']`), gibt es eine **Warnung** und `null`. Deshalb immer `??` oder `isset` benutzen, wenn ein Wert fehlen kann, besonders bei Daten aus `$input`.

### Durchlaufen

```php
foreach ($players as $player) {
    echo $player['name'];
}

foreach ($player as $key => $value) {
    echo "$key: $value\n";
}
```

### Wichtige Array-Funktionen

| Funktion | Was sie macht | Beispiel aus unserem Code |
|---|---|---|
| `count($a)` | Anzahl Einträge | `count($players) >= $settings['max_players']` |
| `array_map($fn, $a)` | Jeden Eintrag umwandeln, neues Array | Spielerliste in `buildState()` |
| `array_filter($a, $fn)` | Nur Einträge behalten, für die `$fn` true liefert. Ohne `$fn`: alle falsy Werte entfernen | Leere SQL-Befehle in `Db::createSchema()` |
| `array_column($a, 'key')` | Aus einer Liste von Arrays eine Spalte holen | `array_column($players, 'join_order')` |
| `array_keys` / `array_values` | Nur Schlüssel / nur Werte | |
| `array_merge($a, $b)` | Zusammenfügen (bei gleichen Text-Schlüsseln gewinnt `$b`) | |
| `array_replace($a, $b)` | Wie `array_merge`, aber auch Zahlen-Schlüssel werden ersetzt | Konfiguration in `bootstrap.php` |
| `in_array($wert, $a, true)` | Enthält das Array diesen Wert? | |
| `array_search($wert, $a, true)` | Schlüssel zu einem Wert (oder `false`) | |
| `sort` / `usort` | Sortieren, **ändert das Original** | |
| `max($a)` / `min($a)` | Größter / kleinster Wert | `max(array_column(...))` |
| `array_sum($a)` | Summe | |
| `shuffle($a)` / `array_rand($a)` | Mischen / zufälliger Schlüssel | |

Achtung: `array_filter` behält die alten Schlüssel. Aus `[0 => 'a', 1 => '', 2 => 'b']` wird `[0 => 'a', 2 => 'b']`. Wer wieder eine Liste ab 0 braucht: `array_values(array_filter(...))`.

### Destructuring und Spread

```php
[$first, $second] = ['A', 'B'];                    // Liste auspacken
['name' => $name, 'avatar' => $avatar] = $player;  // nach Schlüsseln auspacken
$all = [...$listA, ...$listB];                     // Arrays zusammenfügen
```

## 7. Operatoren

### Vergleich: immer `===` benutzen

```php
0 == ''        // false (seit PHP 8; früher true!)
'1' == '01'    // true  – beide sehen nach Zahl aus, werden als Zahl verglichen
'abc' == 0     // false (seit PHP 8)
null == false  // true
1 === 1        // true
'1' === 1      // false – anderer Typ
```

`==` wandelt vorher Typen um und hat viele Sonderfälle. **`===` (und `!==`) vergleicht Wert und Typ** und ist deshalb bei uns Standard. Ausnahme: Zahl mit Zahl (`<`, `>`, `<=`, `>=`) ist unproblematisch.

### Alle wichtigen Operatoren

| Operator | Bedeutung | Beispiel |
|---|---|---|
| `+ - * /` | Rechnen (`/` ergibt evtl. float: `7 / 2` = `3.5`) | |
| `intdiv(7, 2)` | Ganzzahlige Division | `3` |
| `%` | Rest | `7 % 3` = `1` |
| `**` | Potenz | `2 ** 3` = `8` |
| `.` / `.=` | Strings verbinden / anhängen | `'a' . 'b'` |
| `++` / `--` | Um 1 erhöhen / verringern | `$count++` |
| `+=`, `-=`, `*=` | Rechnen und zuweisen | `$score += 2` |
| `=== !==` | Streng gleich / ungleich | |
| `< > <= >=` | Größer/kleiner | |
| `<=>` | „Raumschiff“: −1, 0 oder 1 (für Sortierfunktionen) | `usort($a, fn($x, $y) => $x['score'] <=> $y['score'])` |
| `&& \|\| !` | Und, oder, nicht | |
| `and or` | Auch und/oder, aber mit anderer Rangfolge. **Nicht benutzen.** | |
| `? :` | Bedingung (ternär) | `$ok ? 'ja' : 'nein'` |
| `?:` | „Elvis“: linker Wert, wenn er truthy ist, sonst rechter | `$stmt->fetch() ?: null` |
| `??` | Null-Coalescing: linker Wert, wenn er existiert und nicht `null` ist, sonst rechter. **Keine Warnung**, wenn der Schlüssel fehlt | `$input['name'] ?? ''` |
| `??=` | Nur zuweisen, wenn noch `null`/nicht gesetzt | `$cache ??= load();` |
| `?->` | Nullsafe: Methode nur aufrufen, wenn das Objekt nicht `null` ist | `$lobby?->code` |
| `instanceof` | Ist das Objekt von dieser Klasse? | `$e instanceof ApiError` |
| `@` | Fehlermeldung des Ausdrucks unterdrücken. Nur in Ausnahmefällen | `@touch($marker)` |

Unterschied `?:` und `??` am Beispiel:

```php
$a = 0;
$a ?: 5    // 5 – weil 0 falsy ist
$a ?? 5    // 0 – weil 0 nicht null ist
```

## 8. Kontrollstrukturen

```php
if ($count < self::MIN_PLAYERS) {
    // ...
} elseif ($count > 8) {        // ein Wort: elseif
    // ...
} else {
    // ...
}

for ($i = 0; $i < 4; $i++) { /* ... */ }

while ($running) { /* ... */ }

do {
    $code = $this->randomCode();
} while ($this->findLobby($code) !== null);   // läuft mindestens einmal
```

- `break` verlässt die Schleife, `continue` springt zum nächsten Durchlauf.
- **Geschweifte Klammern immer setzen**, auch wenn nur eine Zeile drinsteht.

### `match` (statt `switch`)

```php
$result = match ($action) {
    'ping' => ['ok' => true],
    'create_lobby', 'new_lobby' => $lobbies->createLobby(...),   // mehrere Werte, ein Ergebnis
    default => throw new ApiError('Unbekannte Aktion.', 404),
};
```

- `match` **gibt einen Wert zurück**, vergleicht streng (`===`) und braucht kein `break`.
- Passt kein Fall und es gibt kein `default`, wirft PHP einen `UnhandledMatchError`.
- `switch` gibt es auch, vergleicht aber mit `==` und braucht `break`. Wir benutzen `match`.

Trick, um Bedingungen statt Werte zu prüfen:

```php
$text = match (true) {
    $mistakes === 0 => 'Perfekt!',
    $mistakes < 5 => 'Gut',
    default => 'Knapp',
};
```

## 9. Funktionen

```php
function greet(string $name, string $greeting = 'Hallo'): string
{
    return "{$greeting}, {$name}!";
}

greet('Lara');                     // 'Hallo, Lara!'
greet('Lara', 'Moin');             // 'Moin, Lara!'
greet(greeting: 'Hi', name: 'Lara'); // benannte Argumente, Reihenfolge egal
```

- Parameter mit Standardwert (`= 'Hallo'`) sind optional und müssen hinten stehen.
- **Variadische Parameter:** `function sum(int ...$numbers): int` nimmt beliebig viele Argumente als Array.
- **Referenzen** mit `&`: `function addPoint(array &$player)` ändert das Original statt einer Kopie. Möglichst vermeiden, das macht Code schwer nachvollziehbar.
- Variablen von außen sind in einer Funktion **nicht** sichtbar. (Ausnahme `global`, wie in `tests/run.php`. Das ist nur dort okay.)

### Anonyme Funktionen (Closures) und Pfeilfunktionen

```php
// Pfeilfunktion: ein einziger Ausdruck, sieht Variablen von außen automatisch
$names = array_map(fn(array $p): string => $p['name'], $players);

// Closure: mehrere Zeilen, Variablen von außen müssen mit "use" übergeben werden
$bonus = 2;
$add = function (int $score) use ($bonus): int {
    return $score + $bonus;
};
$add(5);   // 7
```

`static fn` (wie in `buildState()`) heißt nur: Die Funktion braucht kein `$this`. Das ist eine kleine Optimierung.

**First-class callable:** `strlen(...)` macht aus einer Funktion ein aufrufbares Objekt, z. B. `array_map(trim(...), $list)`.

## 10. Klassen und Objekte

```php
final class LobbyService
{
    public const MIN_PLAYERS = 2;                  // Konstante
    private int $calls = 0;                        // Eigenschaft (Property)

    public function __construct(private readonly PDO $db)
    {
    }

    public function createLobby(string $name, int $avatar): array
    {
        $this->calls++;                            // eigene Eigenschaft
        $code = $this->newLobbyCode();             // eigene Methode
        $max = self::MIN_PLAYERS;                  // eigene Konstante
        // ...
    }

    private function newLobbyCode(): string { /* ... */ }

    public static function version(): string      // statische Methode
    {
        return '1.0';
    }
}

$service = new LobbyService($pdo);   // Objekt erzeugen
$service->createLobby('Felix', 0);   // Methode aufrufen
LobbyService::MIN_PLAYERS;           // Konstante von außen
LobbyService::version();             // statische Methode von außen
```

### Die Pfeile und Doppelpunkte

| Schreibweise | Wann |
|---|---|
| `$objekt->methode()` / `$objekt->eigenschaft` | Zugriff auf ein **Objekt** (Instanz). **Kein `$`** vor dem Eigenschaftsnamen: `$this->db`, nicht `$this->$db` |
| `Klasse::methode()` / `Klasse::KONSTANTE` | Zugriff auf die **Klasse** selbst (statisch) |
| `$this` | Das aktuelle Objekt, innerhalb von Methoden |
| `self::` | Die eigene Klasse (für Konstanten und statische Methoden) |
| `static::` | Wie `self::`, berücksichtigt aber Unterklassen |
| `parent::` | Die Elternklasse, z. B. `parent::__construct($message)` in `ApiError` |
| `=>` | Nur in Arrays (`'key' => $value`), in `match` und in Pfeilfunktionen. Nicht mit `->` verwechseln! |

### Sichtbarkeit

| Schlüsselwort | Wer darf zugreifen |
|---|---|
| `public` | Alle |
| `protected` | Die Klasse und ihre Unterklassen |
| `private` | Nur die Klasse selbst |

Faustregel: Alles `private`, was nicht von außen gebraucht wird. Bei uns sind im `LobbyService` nur die Aktionen `public`, alle Hilfsfunktionen `private`.

### Weitere Schlüsselwörter

| Schlüsselwort | Bedeutung |
|---|---|
| `final class` | Von dieser Klasse darf niemand erben. Bei uns Standard |
| `readonly` | Eigenschaft darf nur einmal (im Konstruktor) gesetzt werden |
| `static` | Gehört zur Klasse, nicht zu einem Objekt. Aufruf mit `::` |
| `abstract` | Klasse/Methode ohne fertige Umsetzung, muss von einer Unterklasse ergänzt werden |
| `extends` | Erbt von einer Klasse: `class ApiError extends RuntimeException` |
| `implements` | Erfüllt ein Interface: `class X implements JsonSerializable` |
| `interface` | Vorgabe, welche Methoden eine Klasse haben muss |
| `enum` | Feste Auswahl von Werten (siehe unten) |
| `new` | Objekt erzeugen |
| `clone` | Objekt kopieren |

### Constructor Property Promotion

```php
public function __construct(private readonly PDO $db)
{
}
```

ist eine Kurzschreibweise für:

```php
private readonly PDO $db;

public function __construct(PDO $db)
{
    $this->db = $db;
}
```

Ein Sichtbarkeits-Schlüsselwort vor einem Konstruktor-Parameter legt also automatisch eine Eigenschaft an. So entsteht auch `$e->status` in `ApiError`.

### Magische Methoden: das Präfix `__`

Methoden, die mit **zwei Unterstrichen** beginnen, ruft PHP selbst auf. Eigene Methoden sollten nie mit `__` anfangen.

| Methode | Wann PHP sie aufruft |
|---|---|
| `__construct()` | Beim Erzeugen mit `new` |
| `__toString()` | Wenn das Objekt als String benutzt wird |
| `__get()` / `__set()` | Beim Zugriff auf nicht vorhandene Eigenschaften |
| `__invoke()` | Wenn das Objekt wie eine Funktion aufgerufen wird: `$obj()` |
| `__destruct()` | Wenn das Objekt gelöscht wird |

### Objekte werden nicht kopiert

Anders als Arrays werden Objekte **nicht** kopiert, wenn man sie weitergibt. Beide Variablen zeigen auf dasselbe Objekt:

```php
$a = new ArrayObject();
$b = $a;
$b->append('x');
count($a);   // 1 – $a hat sich auch geändert
```

### Enums

```php
enum Phase: string
{
    case Lobby = 'lobby';
    case Playing = 'playing';
    case Results = 'results';
}

$phase = Phase::from('lobby');    // Phase::Lobby (Fehler bei unbekanntem Wert)
$phase = Phase::tryFrom('xyz');   // null statt Fehler
$phase->value;                    // 'lobby'
```

Gut für feste Zustände wie die Spielphase. Im Moment speichern wir die Phase noch als einfachen String.

### Namenskonventionen

| Was | Schreibweise | Beispiel |
|---|---|---|
| Klassen, Interfaces, Enums | `PascalCase` | `LobbyService`, `ApiError` |
| Methoden, Funktionen, Variablen | `camelCase` | `joinLobby()`, `$joinOrder` |
| Konstanten | `UPPER_SNAKE_CASE` | `MIN_PLAYERS` |
| Dateien mit einer Klasse | wie die Klasse | `LobbyService.php` |
| Array-Schlüssel und JSON-Felder | `snake_case` | `'player_id'`, `'max_players'` |
| Datenbank-Tabellen und Spalten | `snake_case` | `lobby_code`, `last_seen` |

Gängige Suffixe für Klassennamen: `…Service` (enthält Logik), `…Error`/`…Exception` (Fehler), `…Repository` (nur Datenbankzugriff). Präfixe für Methoden: `is…`/`has…`/`can…` geben `bool` zurück, `get…`/`find…` lesen (bei `find…` darf auch `null` zurückkommen), `check…` prüft und wirft bei Problemen einen Fehler.

## 11. Fehler und Exceptions

```php
try {
    $lobby = $this->findLobby($code) ?? throw new ApiError('Diese Lobby gibt es nicht (mehr).', 404);
} catch (ApiError $e) {
    // nur ApiError abfangen
    echo $e->getMessage();
} catch (Throwable $e) {
    // alles andere
} finally {
    // läuft immer, egal ob Fehler oder nicht
}
```

- **`throw`** wirft einen Fehler. Das Skript springt sofort zum passenden `catch`. Gibt es keins, bricht PHP ab.
- Seit PHP 8 ist `throw` ein Ausdruck und darf z. B. hinter `??` oder `?:` stehen (siehe oben).
- **`Throwable`** fängt alles: `Exception` (z. B. `RuntimeException`, `PDOException`) und `Error` (Programmierfehler wie `TypeError`).
- Wenn man die Variable nicht braucht: `catch (ApiError)` ohne `$e`.

**Unsere Regel:**
- **`ApiError`** für alles, was Spieler sehen sollen („Die Lobby ist voll.“). Text auf Deutsch, verständlich, mit passendem Statuscode (400 falsche Eingabe, 401 Token ungültig, 404 nicht gefunden, 409 Konflikt).
- **Alle anderen Fehler** fängt `api/index.php` ab, schreibt sie ins Log und schickt nur „Interner Serverfehler.“ (online) bzw. die echte Meldung (lokal mit `debug`).

### Transaktionen und Fehler

Wenn mehrere Datenbank-Änderungen zusammengehören, gehört ein `rollBack()` in den `catch`-Block (siehe `joinLobby()`):

```php
$this->db->beginTransaction();
try {
    // mehrere INSERT/UPDATE
    $this->db->commit();
} catch (Throwable $e) {
    $this->db->rollBack();   // alles rückgängig
    throw $e;                // Fehler weiterreichen
}
```

### Debuggen

| Werkzeug | Wofür |
|---|---|
| `var_dump($x)` | Wert **mit Typ** ausgeben. Zerstört die JSON-Antwort, also nur kurz zum Testen |
| `print_r($x)` | Lesbarer, aber ohne Typen |
| `error_log('Text ' . json_encode($x))` | Schreibt ins Terminal, in dem `php -S` läuft. Die JSON-Antwort bleibt heil. **Bevorzugt** |
| `php -l datei.php` | Syntaxfehler finden |
| Test schreiben | Am zuverlässigsten, siehe `server/tests/run.php` |

Fehlermeldungen lesen: `PHP Parse error: syntax error, unexpected token "}" in .../LobbyService.php on line 42` heißt: In Zeile 42 **oder kurz davor** stimmt etwas nicht. Oft fehlt in der Zeile davor ein `;` oder eine Klammer.

## 12. Dateien einbinden, Superglobals, magische Konstanten

### Einbinden

```php
require __DIR__ . '/../src/bootstrap.php';
```

| Befehl | Verhalten |
|---|---|
| `require` | Datei laden. Fehlt sie: Abbruch. **Benutzen wir** |
| `require_once` | Wie `require`, aber nur einmal pro Anfrage |
| `include` | Fehlt die Datei: nur Warnung, Skript läuft weiter |

Eine Datei kann mit `return [...]` einen Wert zurückgeben. So funktioniert `config.php`: `$config = require 'config.php';`

**Pfade immer mit `__DIR__` bauen.** Relative Pfade wie `'../src/x.php'` beziehen sich auf den Ordner, aus dem PHP gestartet wurde, nicht auf die Datei. Das geht schnell schief.

### Magische Konstanten (Präfix und Suffix `__`)

| Konstante | Wert |
|---|---|
| `__DIR__` | Ordner der aktuellen Datei |
| `__FILE__` | Pfad der aktuellen Datei |
| `__LINE__` | Aktuelle Zeilennummer |
| `__CLASS__` / `__FUNCTION__` / `__METHOD__` | Name der aktuellen Klasse/Funktion/Methode |
| `PHP_VERSION`, `PHP_EOL` | Version, Zeilenumbruch des Systems |

### Superglobals (Präfix `$_`)

Diese Variablen füllt PHP selbst und sie sind überall verfügbar:

| Variable | Inhalt | Bei uns |
|---|---|---|
| `$_GET` | Parameter aus der URL (`?action=ping`) | `$_GET['action']` |
| `$_POST` | Formulardaten. **Nicht** JSON! | Nicht benutzt |
| `$_SERVER` | Infos über Anfrage und Server (`REQUEST_METHOD`, `HTTP_HOST`, …) | `$_SERVER['REQUEST_METHOD']` |
| `$_COOKIE`, `$_SESSION`, `$_FILES`, `$_ENV` | Cookies, Sitzungen, Uploads, Umgebung | Nicht benutzt |

JSON im Body steht **nicht** in `$_POST`. Man liest es selbst:

```php
$input = json_decode(file_get_contents('php://input') ?: '{}', true);
```

**Alles in Superglobals kommt vom Nutzer und kann gefälscht sein.** Nie ungeprüft benutzen, siehe [Sicherheit](#18-sicherheit).

### Namespaces und `use`

Größere Projekte ordnen Klassen in Namespaces (`namespace Pangmam;`, `use Pangmam\LobbyService;`, `\PDO`). Wir benutzen **keine** Namespaces, weil es nur wenige Klassen gibt. Wenn dir in Beispielen aus dem Internet `\PDO` mit Backslash begegnet: Das heißt nur „die PDO-Klasse aus dem globalen Namespace“.

## 13. Datenbank mit PDO

PDO ist die eingebaute Datenbank-Schnittstelle von PHP. Dieselben Befehle funktionieren mit SQLite (lokal) und MySQL (Webspace).

### Abfragen mit Platzhaltern

```php
// 1. Befehl mit ? als Platzhalter vorbereiten
$stmt = $this->db->prepare('SELECT * FROM players WHERE lobby_code = ? ORDER BY join_order');
// 2. Werte für die ? in derselben Reihenfolge einsetzen und ausführen
$stmt->execute([$code]);
// 3. Ergebnis holen
$players = $stmt->fetchAll();   // alle Zeilen als Liste von Arrays
$player = $stmt->fetch();       // nur die nächste Zeile – oder false, wenn keine da ist
$count = $stmt->fetchColumn();  // nur der erste Wert der ersten Zeile, z. B. bei SELECT COUNT(*)
```

Benannte Platzhalter gehen auch:

```php
$stmt = $this->db->prepare('UPDATE players SET score = :score WHERE id = :id');
$stmt->execute(['score' => 5, 'id' => $playerId]);
```

**Niemals Werte direkt in den SQL-String einbauen:**

```php
// ❌ SQL-Injection! Jemand schickt als Code:  ' OR '1'='1
$this->db->query("SELECT * FROM lobbies WHERE code = '$code'");

// ✔ Platzhalter
$stmt = $this->db->prepare('SELECT * FROM lobbies WHERE code = ?');
$stmt->execute([$code]);
```

### Typen aus der Datenbank

Werte aus der Datenbank kommen je nach Datenbank als **String** an, auch Zahlen (`'3'` statt `3`). Deshalb wandeln wir in `buildState()` mit `(int)` um, bevor sie ins JSON gehen. Mit `strict_types` und `===` fällt so etwas sonst als Bug auf: `'3' === 3` ist `false`.

### Sonst wichtig

- `fetch()` liefert bei keinem Treffer `false`, nicht `null`. Daher `$stmt->fetch() ?: null`.
- `$this->db->lastInsertId()` gibt die ID der zuletzt eingefügten Zeile (nur bei Auto-Increment). Wir erzeugen IDs selbst mit `random_bytes`.
- `$stmt->rowCount()` sagt, wie viele Zeilen ein `UPDATE`/`DELETE` geändert hat.
- **Neue Spalten:** in `server/src/schema.sql` eintragen. Lokal `data/pangmam.sqlite` löschen. Auf dem Webspace zusätzlich ein `ALTER TABLE` ausführen (siehe Kommentar in `schema.sql`).
- In `schema.sql` nur SQL schreiben, das **SQLite und MySQL** beide verstehen, und Kommentare nur als eigene Zeilen mit `--`.

## 14. JSON

```php
// PHP → JSON-Text
json_encode(['ok' => true, 'name' => 'Bär'], JSON_UNESCAPED_UNICODE);
// '{"ok":true,"name":"Bär"}'

// JSON-Text → PHP
json_decode('{"name":"Felix"}', true);   // ['name' => 'Felix']   ← mit true: Array
json_decode('{"name":"Felix"}');         // stdClass-Objekt       ← ohne true: Objekt, umständlich
json_decode('kaputt', true);             // null
```

- **Immer `true` als zweites Argument** an `json_decode` übergeben, dann bekommt man Arrays.
- Bei ungültigem JSON kommt `null` zurück. Daher prüft `index.php` mit `is_array($input)`.
- Für Fehler statt `null`: `json_decode($text, true, flags: JSON_THROW_ON_ERROR)`.
- Ein **leeres** PHP-Array `[]` wird zu `[]` in JSON, auch wenn es ein Wörterbuch sein sollte. Wer sicher ein JSON-Objekt `{}` will: `(object) []` oder `new stdClass()`.
- Zuordnung zu Godot: PHP-Array mit Schlüsseln → `Dictionary`, PHP-Liste → `Array`, `null` → `null`.

## 15. Wichtige eingebaute Funktionen

| Funktion | Wofür |
|---|---|
| `time()` | Aktuelle Zeit als Unix-Zeitstempel (Sekunden seit 1970). Speichern wir in `updated_at`, `last_seen` |
| `date('d.m.Y H:i', $ts)` | Zeitstempel als lesbarer Text |
| `random_int($min, $max)` | Sichere Zufallszahl (inklusive beider Grenzen). Für alles, was nicht erratbar sein soll |
| `random_bytes($n)` + `bin2hex()` | Zufällige Bytes → Hex-Text, z. B. für Tokens |
| `rand()` / `mt_rand()` | Einfache Zufallszahlen. **Nicht** für Geheimnisse benutzen |
| `json_encode` / `json_decode` | Siehe [JSON](#14-json) |
| `file_get_contents` / `file_put_contents` | Datei lesen/schreiben |
| `file_exists($pfad)` | Gibt es die Datei? |
| `http_response_code(404)` | HTTP-Statuscode setzen |
| `header('Name: Wert')` | HTTP-Header setzen. **Muss vor jeder Ausgabe passieren** |
| `error_log($text)` | Ins Server-Log schreiben |
| `isset` / `empty` / `unset` | Gesetzt? / Leer oder falsy? / Löschen |

Funktionsnamen in PHP folgen oft einem Präfix-Schema, an dem man erkennt, worum es geht: `str_…` und `mb_…` (Strings), `array_…` (Arrays), `json_…`, `is_…` (Typ prüfen, gibt `bool` zurück), `preg_…` (reguläre Ausdrücke), `file_…`, `random_…`. Die Reihenfolge der Parameter ist leider nicht einheitlich (`in_array($nadel, $heuhaufen)`, aber `str_contains($heuhaufen, $nadel)`). Im Zweifel auf php.net nachschauen.

## 16. Präfixe, Suffixe und Sonderzeichen auf einen Blick

| Zeichen | Bedeutung | Beispiel |
|---|---|---|
| `<?php` | Beginn des PHP-Codes | erste Zeile jeder Datei |
| `$name` | Variable | `$code` |
| `$this` | Aktuelles Objekt | `$this->db` |
| `$_GET`, `$_SERVER` | Superglobals | `$_GET['action']` |
| `->` | Objekt-Zugriff | `$stmt->execute()` |
| `?->` | Objekt-Zugriff, nur wenn nicht `null` | `$lobby?->code` |
| `::` | Klassen-Zugriff (statisch, Konstanten) | `self::CODE_LENGTH`, `Db::connect()` |
| `=>` | Schlüssel → Wert (Array, `match`, `fn`) | `'ok' => true` |
| `.` / `.=` | Strings verbinden | `'a' . 'b'` |
| `??` / `??=` | Standardwert bei `null`/nicht gesetzt | `$input['name'] ?? ''` |
| `?:` | Standardwert bei falsy | `$x ?: null` |
| `===` / `!==` | Streng gleich / ungleich | |
| `?Typ` | Typ oder `null` | `?string` |
| `A\|B` | Typ A oder B | `int\|string` |
| `...` | Spread / variadische Parameter / first-class callable | `[...$a, ...$b]`, `trim(...)` |
| `&` | Referenz (Original statt Kopie) | `function f(array &$a)` |
| `@` | Warnung unterdrücken | `@touch($marker)` |
| `\` | Namespace-Trenner | `\PDO` |
| `#[...]` | Attribut (Metadaten für Klassen/Funktionen) | `#[\ReturnTypeWillChange]` |
| `__construct`, `__toString` | Magische Methoden (zwei Unterstriche) | |
| `__DIR__`, `__LINE__` | Magische Konstanten | `__DIR__ . '/schema.sql'` |
| `fn(...) =>` | Pfeilfunktion | `fn($p) => $p['name']` |
| `<<<SQL ... SQL;` | Heredoc (mehrzeiliger String) | |
| `/** ... */` | Doc-Kommentar | vor jeder Methode |
| `.php` | PHP-Datei | |
| `.sql` | SQL-Befehle | `schema.sql` |
| `.htaccess` | Regeln für den Apache-Webserver (Zugriff sperren usw.) | `src/.htaccess` |
| `.sqlite` | SQLite-Datenbankdatei, **nicht committen** | `data/pangmam.sqlite` |

## 17. Typische Stolperfallen

1. **`;` vergessen.** Die Fehlermeldung zeigt dann oft auf die **nächste** Zeile.
2. **`$` vergessen** (`name` statt `$name`). PHP hält das für eine Konstante und wirft einen Fehler.
3. **`$` zu viel** (`$this->$db` statt `$this->db`). Das liest den Wert von `$db` als Eigenschaftsnamen.
4. **`+` statt `.`** zum Verbinden von Strings.
5. **`'...'` statt `"..."`**, wenn eine Variable eingesetzt werden soll.
6. **`==` statt `===`.** Lieber immer `===`.
7. **`strlen`/`strtoupper` bei Umlauten** statt `mb_strlen`/`mb_strtoupper`.
8. **Fehlender Array-Schlüssel** → Warnung. Mit `??` absichern.
9. **`fetch()` gibt `false`**, nicht `null`, wenn nichts gefunden wurde.
10. **Zahlen aus der Datenbank sind Strings.** Mit `(int)` umwandeln.
11. **Ausgabe vor `header()`** (z. B. ein `var_dump` oder Leerzeichen vor `<?php`) → „headers already sent“.
12. **`json_decode` ohne `true`** → Objekte statt Arrays.
13. **`empty('0')` ist `true`.** Wer prüfen will, ob ein String leer ist: `$s === ''`.
14. **`array_filter` behält Schlüssel.** Bei Bedarf `array_values()` drumherum.
15. **`sort()` gibt nicht das sortierte Array zurück**, sondern ändert das Original und gibt `true` zurück.
16. **Variablen „überleben“ keine Anfrage.** Alles, was bleiben soll, in die Datenbank.
17. **Fehler im Spiel sehen, aber nicht wo?** Im Terminal von `php -S` stehen die Details.

## 18. Sicherheit

Der Server ist unsere einzige Verteidigung gegen Schummeln. Jeder kann im Browser beliebige Anfragen an `/api/` schicken.

- **Allem misstrauen, was vom Spiel kommt.** Längen prüfen, Typen umwandeln, erlaubte Werte prüfen (wie `checkName()` und `checkAvatar()`).
- **Immer Platzhalter in SQL** (`?`), nie Werte in den SQL-String einbauen.
- **Bei jeder Aktion prüfen, ob der Spieler das darf:** Ist er in dieser Lobby (Token)? Ist er Host? Ist er an der Reihe? Ist die Phase richtig?
- **Nichts verraten, was der Spieler nicht wissen darf.** Das gesuchte Wort geht nur an den Wortgeber. Ratende bekommen nur `_ _ A _`. Tokens der anderen Spieler werden nie mitgeschickt. Alles das passiert in `buildState()`.
- **Geheimnisse mit `random_bytes`/`random_int`** erzeugen, nicht mit `rand()`.
- **`config.php` nie committen.** Sie enthält das Datenbank-Passwort und steht in `.gitignore`.
- **Online keine technischen Fehlerdetails ausgeben** (`debug => false`).

## 19. Rezept: eine neue API-Aktion hinzufügen

Beispiel: Der Host startet das Spiel (`start_game`).

1. **In `docs/api.md` beschreiben:** Name, was rein geht, was raus kommt, mögliche Fehler. Das ist der Vertrag mit dem Spiel.
2. **Methode im `LobbyService` schreiben:**
   ```php
   public function startGame(string $token): array
   {
       $player = $this->playerByToken($token);
       $lobby = $this->findLobby($player['lobby_code']);
       if ($lobby['host_id'] !== $player['id']) {
           throw new ApiError('Nur der Host kann das Spiel starten.', 403);
       }
       if (count($this->playersOf($lobby['code'])) < self::MIN_PLAYERS) {
           throw new ApiError('Ihr braucht mindestens zwei Spieler.', 409);
       }
       $this->db->prepare('UPDATE lobbies SET phase = ? WHERE code = ?')
           ->execute(['playing', $lobby['code']]);
       $this->bumpVersion($lobby['code']);   // ganz wichtig: sonst merken die anderen nichts
       return ['ok' => true];
   }
   ```
3. **In `api/index.php` im `match` eintragen:**
   ```php
   'start_game' => $lobbies->startGame($input['token'] ?? ''),
   ```
4. **Test in `server/tests/run.php` schreiben**, für den Erfolgsfall **und** die Fehlerfälle:
   ```php
   test('Nur der Host darf starten', function () {
       $s = freshService();
       $host = $s->createLobby('Felix', 0);
       $lara = $s->joinLobby($host['code'], 'Lara', 0);
       expectError(fn() => $s->startGame($lara['token']), 'Fehler, wenn Nicht-Host startet');
       check($s->startGame($host['token'])['ok'], 'Host darf starten');
   });
   ```
5. **Testen:** `php server/tests/run.php` und einmal mit dem Spiel ausprobieren.
6. **Im Spiel aufrufen:** `await Api.call_action("start_game", {"token": Session.token})`.

**Merke:** Jede Änderung an einer Lobby braucht ein `bumpVersion()`, sonst bekommen die anderen Spieler den neuen Stand nie.

## 20. Unsere Regeln (Kurzfassung)

- `declare(strict_types=1);` in jeder Datei.
- 4 Leerzeichen zum Einrücken, keine Tabs.
- Eine Klasse pro Datei in `server/src/`, Dateiname = Klassenname. Neue Dateien in `bootstrap.php` mit `require` eintragen.
- Typen bei allen Parametern und Rückgabewerten.
- `===` statt `==`, `'...'` statt `"..."` (außer bei Ersetzung), `match` statt `switch`.
- Namen im Code auf Englisch, Kommentare und Fehlermeldungen auf Deutsch.
- Fehler für Spieler als `ApiError` mit verständlichem Text.
- Jede neue Aktion: `docs/api.md` + Test.
- Vor dem Commit: `php server/tests/run.php`. Die CI prüft zusätzlich die Syntax aller Dateien.

## 21. Zum Weiterlesen

- [Offizielles PHP-Handbuch (deutsch)](https://www.php.net/manual/de/). Zu jeder Funktion gibt es eine Seite: `php.net/<funktionsname>`, z. B. [php.net/array_map](https://www.php.net/array_map)
- [PHP: The Right Way](https://phptherightway.com/) (englisch): moderne Praxis in Kurzform
- [PDO-Tutorial](https://phpdelusions.net/pdo) (englisch): alles über Datenbankzugriff
- Unsere Dateien zum Nachlesen: [`api/index.php`](../server/api/index.php) → [`LobbyService.php`](../server/src/LobbyService.php) → [`tests/run.php`](../server/tests/run.php)
