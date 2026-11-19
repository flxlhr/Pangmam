# GDScript-Grundlagen für Pangmam

Alles, was du brauchst, um am Spiel (`game/`) mitzuarbeiten. Die Beispiele stammen so weit wie möglich aus unserem eigenen Code.
Für den Server gibt es ein eigenes Dokument: [php-grundlagen.md](php-grundlagen.md).

**Videos zum Einstieg**

Wer lieber erst zuschaut und dann hier nachliest:

| Sprache | Video | Kanal | Länge | Hinweis |
|---|---|---|---|---|
| 🇩🇪 Deutsch | [Godot Engine 4 Tutorial für Anfänger – Teil 1](https://www.youtube.com/watch?v=QwSo0gLr_8Q) | TruelyMostWanted | Reihe | Godot 4, Einstieg in Editor, Szenen und Skripte |
| 🇩🇪 Deutsch | [Programmieren lernen mit Godot Engine – GDScript](https://www.youtube.com/playlist?list=PLUvWJlUX-199w8lqhYoER2qzGsirhKb9E) | Thomas Coding Games | 5 kurze Folgen | Variablen, if, Schleifen, Arrays. **Noch Godot 3** (2020): die Grundlagen stimmen, aber Details wie `@export` und Signale sehen in Godot 4 anders aus |
| 🇬🇧 Englisch | [How to program in Godot – GDScript Tutorial](https://www.youtube.com/watch?v=e1zJS31tr88) | Brackeys | ca. 1 h | **Beste Einführung**: Godot 4, alle Grundlagen inkl. Signale und Typen in einem Video |
| 🇬🇧 Englisch | [Godot 4 GDScript Tutorial – Part 1](https://www.youtube.com/watch?v=9xQtIzPW73w) | Omogonix | Reihe aus kurzen Folgen | Godot 4, Schritt für Schritt, gut zum Nachschlagen einzelner Themen |
| 🇬🇧 Englisch | [Learn GDScript From Zero](https://gdquest.github.io/learn-gdscript/) | GDQuest | interaktiv | Kein Video, sondern ein kostenloser Kurs im Browser mit Übungen. Ideal, wenn man noch nie programmiert hat |

Beim Suchen nach weiteren Videos auf **„Godot 4“** achten. Videos zu Godot 3 benutzen oft Befehle, die es nicht mehr gibt (siehe [Werkzeuge](#2-werkzeuge-und-tastenkürzel)).

**Inhalt**

1. [Godot in fünf Begriffen](#1-godot-in-fünf-begriffen)
2. [Werkzeuge und Tastenkürzel](#2-werkzeuge-und-tastenkürzel)
3. [Aufbau eines Skripts](#3-aufbau-eines-skripts)
4. [Einrückung, Zeilen und Kommentare](#4-einrückung-zeilen-und-kommentare)
5. [Variablen, Konstanten und Typen](#5-variablen-konstanten-und-typen)
6. [Strings](#6-strings)
7. [Arrays und Dictionaries](#7-arrays-und-dictionaries)
8. [Operatoren](#8-operatoren)
9. [Kontrollstrukturen](#9-kontrollstrukturen)
10. [Funktionen](#10-funktionen)
11. [Knoten und der Szenenbaum](#11-knoten-und-der-szenenbaum)
12. [Signale](#12-signale)
13. [await: auf etwas warten](#13-await-auf-etwas-warten)
14. [Annotations (`@`)](#14-annotations-)
15. [Klassen, Enums, Setter und Getter](#15-klassen-enums-setter-und-getter)
16. [Autoloads: unsere drei Helfer](#16-autoloads-unsere-drei-helfer)
17. [Dateien, Pfade und Ressourcen](#17-dateien-pfade-und-ressourcen)
18. [Präfixe, Suffixe und Sonderzeichen auf einen Blick](#18-präfixe-suffixe-und-sonderzeichen-auf-einen-blick)
19. [Namenskonventionen und Reihenfolge im Skript](#19-namenskonventionen-und-reihenfolge-im-skript)
20. [Debuggen](#20-debuggen)
21. [Typische Stolperfallen](#21-typische-stolperfallen)
22. [Rezept: einen neuen Bildschirm oder eine Komponente bauen](#22-rezept-einen-neuen-bildschirm-oder-eine-komponente-bauen)
23. [Unsere Regeln (Kurzfassung)](#23-unsere-regeln-kurzfassung)
24. [Zum Weiterlesen](#24-zum-weiterlesen)

---

## 1. Godot in fünf Begriffen

GDScript ist die eigene Skriptsprache von Godot. Sie sieht aus wie Python, ist aber eng mit der Engine verbunden. Ohne diese fünf Begriffe versteht man den Code nicht:

| Begriff | Was es ist | Bei uns |
|---|---|---|
| **Knoten (Node)** | Baustein mit einer Aufgabe: `Label` zeigt Text, `Button` ist ein Knopf, `Timer` zählt, `HTTPRequest` schickt Anfragen | `%CreateButton`, `%PlayerList` |
| **Szene** | Ein Baum aus Knoten, gespeichert als `.tscn`. Kann ein ganzer Bildschirm oder ein kleines Teil sein | `start_screen.tscn` |
| **Skript** | `.gd`-Datei, die an einen Knoten gehängt wird und ihm Verhalten gibt | `start_screen.gd` hängt am obersten Knoten von `start_screen.tscn` |
| **Signal** | Nachricht, die ein Knoten aussendet, wenn etwas passiert („wurde gedrückt“). Andere können darauf hören | `pressed`, `Session.state_changed` |
| **Autoload** | Skript, das beim Start automatisch geladen wird und überall unter einem Namen erreichbar ist | `Api`, `Session`, `Screens` |

Der **Szenenbaum** (Scene Tree) ist alles, was gerade läuft: oben die Autoloads, darunter die aktuelle Szene (z. B. die Lobby) mit all ihren Knoten.

```
root
├── Api          (Autoload)
├── Session      (Autoload)
├── Screens      (Autoload)
└── LobbyScreen  (aktuelle Szene, wird von Screens.goto() ausgetauscht)
    ├── CodeLabel
    ├── PlayerList
    └── ...
```

## 2. Werkzeuge und Tastenkürzel

| Was | Wie |
|---|---|
| Projekt starten | **F5** (Hauptszene = Startbildschirm) |
| Aktuelle Szene starten | **F6**. Gut, um einen Bildschirm einzeln zu testen |
| Stoppen | **F8** |
| Mehrere Spieler testen | *Debug → Instanzen ausführen → 2*, dann öffnen sich zwei Fenster |
| Haltepunkt setzen | Im Skript links neben die Zeilennummer klicken oder **F9** |
| Hilfe zu einer Klasse/Funktion | **Strg/Cmd + Klick** auf den Namen, oder im Skript-Editor *Hilfe durchsuchen* |
| Formatieren und prüfen | `gdformat game/` und `gdlint game/` (Installation: `pip install "gdtoolkit==4.5.0"`) |

Die CI auf GitHub prüft bei jedem Pull Request mit `gdlint` und `gdformat --check` und lädt das Projekt einmal in Godot. **Vor dem Commit lokal `gdformat game/` laufen lassen**, dann ist die Formatierung garantiert richtig.

Wir benutzen **Godot 4.7, Standard-Version (nicht .NET)**. Beispiele aus dem Internet für **Godot 3** funktionieren oft nicht (z. B. `yield` statt `await`, `connect("pressed", self, "_on_x")` statt `pressed.connect(_on_x)`, `export` statt `@export`). Beim Suchen immer „Godot 4“ dazuschreiben.

## 3. Aufbau eines Skripts

```gdscript
class_name Keyboard          # optional: macht das Skript als Typ "Keyboard" überall bekannt
extends HBoxContainer        # von welchem Knotentyp das Skript erbt (Pflicht)
## Bildschirm-Tastatur: zeigt A–Z und meldet, welcher Buchstabe gedrückt wurde.

signal letter_pressed(letter: String)

const LETTERS := "ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜ"

@export var button_size := 48

var _used_letters: Array[String] = []


func _ready() -> void:
	for letter in LETTERS:
		_add_button(letter)


func _add_button(letter: String) -> void:
	var button := Button.new()
	button.text = letter
	button.custom_minimum_size = Vector2(button_size, button_size)
	button.pressed.connect(_on_letter_button_pressed.bind(letter))
	add_child(button)


func _on_letter_button_pressed(letter: String) -> void:
	_used_letters.append(letter)
	letter_pressed.emit(letter)
```

- **`extends`** legt fest, welcher Knotentyp das Skript ist. Ein Skript mit `extends Control` muss an einem `Control`-Knoten (oder einem Untertyp wie `Button`) hängen. Alle Methoden des Typs stehen dann direkt zur Verfügung (`add_child`, `visible`, `size`, …).
- **Ein Skript ist eine Klasse.** Variablen oben sind die Eigenschaften, Funktionen die Methoden.
- **Zwischen Funktionen stehen zwei Leerzeilen.** Das verlangt der Style Guide, `gdformat` macht es automatisch.

## 4. Einrückung, Zeilen und Kommentare

### Einrückung ist Syntax

Es gibt **keine geschweiften Klammern**. Was zu einem `if`, einer Schleife oder Funktion gehört, erkennt Godot nur an der **Einrückung**:

```gdscript
func _show_state(state: Dictionary) -> void:
	%PlayerList.clear()                     # gehört zur Funktion
	for player: Dictionary in state.get("players", []):
		var line: String = player["name"]   # gehört zur Schleife
		if player["id"] == host_id:
			line += "  (Host)"              # gehört zum if
		%PlayerList.add_item(line)          # wieder Schleife
```

- Wir rücken mit **Tabs** ein (Godot-Standard). Niemals Tabs und Leerzeichen mischen, das gibt den Fehler „Mixed use of tabs and spaces“.
- Jede Zeile, die einen Block öffnet (`func`, `if`, `for`, `while`, `match`, `class`), endet mit **`:`**.
- Ein leerer Block braucht `pass`:
  ```gdscript
  func _process(_delta: float) -> void:
  	pass
  ```
- **Kein `;`** am Zeilenende (es ist erlaubt, aber unüblich).

### Lange Zeilen

Innerhalb von Klammern `( [ {` darf man frei umbrechen. Sonst mit `\` am Zeilenende:

```gdscript
var res: Dictionary = await Api.call_action(
	"create_lobby", {"name": player_name, "avatar": avatar}
)
var ok := is_host \
	and players.size() >= 2
```

`gdlint` erlaubt höchstens **100 Zeichen pro Zeile**.

### Kommentare

```gdscript
# Normaler Kommentar: erklärt den Code darunter.

## Doc-Kommentar: beschreibt die Klasse, Variable oder Funktion direkt darunter.
## Godot zeigt ihn in der eingebauten Hilfe und beim Überfahren mit der Maus an.
func is_host() -> bool:
	return state.get("lobby", {}).get("host_id", "") == player_id

#region Hilfsfunktionen      ← lässt sich im Editor zuklappen
#endregion
```

Mehrzeilige Kommentare gibt es nicht. Einfach jede Zeile mit `#` beginnen (im Editor: Zeilen markieren, **Strg/Cmd + K**).

## 5. Variablen, Konstanten und Typen

### Variablen

```gdscript
var player_name = ""             # ohne Typ (dynamisch) – vermeiden
var player_name: String = ""     # mit Typ
var player_name := ""            # Typ wird aus dem Wert abgeleitet (hier String) – kurz und typsicher
```

- **`:=`** heißt: „Leg den Typ anhand des Wertes fest.“ Danach kann die Variable nur noch diesen Typ enthalten.
- **Wir geben immer einen Typ an** (`: Typ` oder `:=`). Godot findet dann Fehler schon beim Tippen, und der Code läuft schneller.
- Variablen außerhalb von Funktionen sind **Eigenschaften** des Knotens und leben so lange wie der Knoten. Variablen in Funktionen gibt es nur während des Aufrufs.

### Konstanten

```gdscript
const POLL_INTERVAL := 1.0
const LOBBY := "res://screens/lobby/lobby_screen.tscn"
```

Konstanten können nicht geändert werden und werden in `GROSSBUCHSTABEN` geschrieben. Von außen: `Screens.LOBBY`, `Session.AVATAR_COUNT`.

### Die wichtigsten Typen

| Typ | Beispiel | Bemerkung |
|---|---|---|
| `int` | `42`, `-1`, `1_000_000` | Ganze Zahl (64 Bit). `_` als Tausendertrenner erlaubt |
| `float` | `3.14`, `1.0`, `1e-3` | Kommazahl (Punkt!) |
| `bool` | `true`, `false` | |
| `String` | `"Hallo"`, `'Hallo'` | Text (Unicode, Umlaute funktionieren einfach) |
| `StringName` | `&"pressed"` | Eindeutiger Name, schneller zu vergleichen. Godot benutzt ihn intern für Signal- und Methodennamen |
| `NodePath` | `^"Panel/Label"` | Pfad zu einem Knoten |
| `Array` | `[1, "a", true]` | Liste, siehe [Arrays](#7-arrays-und-dictionaries) |
| `Array[String]` | `["A", "B"]` | Liste, die nur Strings enthalten darf |
| `Dictionary` | `{"name": "Felix"}` | Wörterbuch |
| `Vector2` | `Vector2(10, 20)` | Position/Größe in 2D (`.x`, `.y`) |
| `Color` | `Color.RED`, `Color("#ff8800")` | Farbe |
| `PackedStringArray` | `PackedStringArray(["a"])` | Speichersparende Liste nur für Strings (es gibt auch `PackedByteArray`, `PackedInt32Array`, …) |
| `Callable` | `_on_pressed`, `func(): pass` | Etwas Aufrufbares (Funktion), siehe [Funktionen](#10-funktionen) |
| `Signal` | `pressed` | Ein Signal als Wert |
| `Variant` | – | „Irgendein Typ“. Rückgabe von z. B. `JSON.parse_string()` oder `dict["key"]` |
| `null` | `null` | „Nichts“. Nur Objekte (Knoten usw.) und `Variant` können `null` sein. Ein `int` oder `String` nie |
| Knoten-Typen | `Label`, `Button`, `Node`, `Control` | Objekte; werden per Referenz weitergegeben |

### Typen umwandeln und prüfen

```gdscript
int("42")         # 42
int(3.9)          # 3 (schneidet ab)
float("1.5")      # 1.5
str(42)           # "42"
"42".to_int()     # 42
String.num(3.14159, 2)  # "3.14"

typeof(x) == TYPE_DICTIONARY   # Grundtyp prüfen (so macht es api.gd)
x is Button                    # Ist x ein Button (oder Untertyp)?
var b := x as Button           # Umwandeln; bei Objekten null, wenn es nicht passt
```

### Was als „falsch“ gilt

In `if`-Bedingungen gelten `false`, `0`, `0.0`, `""`, `[]`, `{}` und `null` als falsch. Bei Objekten: `null` oder bereits gelöschte Objekte. Lesbarer ist aber meist der ausdrückliche Vergleich: `if token != "":` oder `if list.is_empty():`.

## 6. Strings

```gdscript
var name := "Lara"
var text := "Hallo " + name + "!"   # Verbinden mit +
text += " Willkommen."              # Anhängen
```

- `"..."` und `'...'` sind **gleichwertig**. Wir benutzen `"..."`.
- Anders als in PHP wird nichts automatisch eingesetzt. `"Hallo $name"` ergibt wörtlich `Hallo $name`.
- **`+` geht nur mit Strings.** `"Spieler: " + 3` ist ein Fehler. Richtig: `"Spieler: " + str(3)`.
- Mehrzeilig: `"""…"""`. Escapes: `\n` (Zeilenumbruch), `\t`, `\"`, `\\`.
- Umlaute sind in Godot normale Zeichen: `"Bär".length()` ist `3`, `"bär".to_upper()` ist `"BÄR"`.

### Formatieren mit `%`

```gdscript
"Avatar %d" % (Session.avatar + 1)                 # %d = ganze Zahl
"%d Spieler. Warte auf den Host …" % count
"%s?lobby=%s" % [str(page), lobby_code]            # mehrere Werte als Array
"Fehler (HTTP %d)." % status
"%.2f Sekunden" % 1.23456                          # %.2f = 2 Nachkommastellen → "1.23"
"%03d" % 7                                         # mit Nullen auffüllen → "007"
```

| Platzhalter | Für |
|---|---|
| `%s` | Beliebiger Wert als Text |
| `%d` | Ganze Zahl |
| `%f` / `%.2f` | Kommazahl / mit 2 Nachkommastellen |
| `%%` | Ein echtes `%` |

Alternative mit Namen: `"{name} hat {points} Punkte".format({"name": "Lara", "points": 3})`

### Nützliche String-Methoden

| Methode | Beispiel | Ergebnis |
|---|---|---|
| `length()` | `"Bär".length()` | `3` |
| `is_empty()` | `"".is_empty()` | `true` |
| `strip_edges()` | `"  K7QX ".strip_edges()` | `"K7QX"` |
| `to_upper()` / `to_lower()` | `"k7qx".to_upper()` | `"K7QX"` |
| `contains(teil)` | `"Hangman".contains("man")` | `true` |
| `begins_with` / `ends_with` | `"res://x.gd".ends_with(".gd")` | `true` |
| `replace(alt, neu)` | `"a-b".replace("-", " ")` | `"a b"` |
| `split(trenner)` | `"a,b".split(",")` | `["a", "b"]` (`PackedStringArray`) |
| `", ".join(liste)` | `", ".join(["a", "b"])` | `"a, b"` |
| `substr(start, länge)` | `"Pangmam".substr(0, 4)` | `"Pang"` |
| `find(teil)` | `"Hangman".find("g")` | `3` (oder `-1`) |
| `text[i]` | `"Hallo"[0]` | `"H"` |
| `uri_encode()` | `"a b".uri_encode()` | `"a%20b"` (für URLs) |
| `nocasecmp_to(other)` | `"a".nocasecmp_to("A")` | `0` (gleich, ohne Groß/Klein) |

Ein `for`-Schleife über einen String liefert die einzelnen Zeichen: `for letter in "ABC":`.

## 7. Arrays und Dictionaries

### Array (Liste)

```gdscript
var letters := ["A", "B", "C"]
letters[0]               # "A" – Zählung beginnt bei 0
letters[-1]              # "C" – negativ zählt von hinten
letters.append("D")      # hinten anhängen (auch: push_back)
letters.size()           # 4
letters.has("B")         # true (auch: "B" in letters)
letters.erase("B")       # ersten Eintrag mit diesem Wert entfernen
letters.remove_at(0)     # Eintrag an Position 0 entfernen
letters.is_empty()       # false
letters.clear()          # alles löschen

var scores: Array[int] = [3, 1, 2]   # typisiertes Array: nur ints erlaubt
scores.sort()                        # ändert das Array selbst → [1, 2, 3]
```

Funktionale Helfer (geben ein neues Array zurück bzw. einen Wert):

```gdscript
var names := players.map(func(p: Dictionary) -> String: return p["name"])
var online := players.filter(func(p: Dictionary) -> bool: return p["connected"])
var total: int = scores.reduce(func(sum: int, s: int) -> int: return sum + s, 0)
var anyone_offline := players.any(func(p: Dictionary) -> bool: return not p["connected"])
players.sort_custom(func(a: Dictionary, b: Dictionary) -> bool: return a["score"] > b["score"])
letters.pick_random()    # zufälliger Eintrag
letters.shuffle()        # mischen
```

### Dictionary (Wörterbuch)

```gdscript
var player := {"name": "Felix", "avatar": 3}
player["name"]                    # "Felix"
player.name                       # "Felix" – Kurzform, nur für String-Schlüssel
player["score"] = 10              # setzen oder hinzufügen
player.get("score", 0)            # Wert oder Standardwert, wenn der Schlüssel fehlt
player.has("score")               # true (auch: "score" in player)
player.erase("score")             # entfernen
player.keys()                     # ["name", "avatar"]
player.values()                   # ["Felix", 3]
player.size()                     # 2
for key in player:                # über die Schlüssel laufen
	print(key, " = ", player[key])
```

- **Fehlende Schlüssel:** `player["xyz"]` und `player.xyz` geben einen **Fehler**. Wenn ein Wert fehlen kann, immer `.get("xyz", standardwert)` benutzen. So macht es `Session` überall: `state.get("lobby", {}).get("host_id", "")`.
- Die Antworten der API (`Api.call_action`) und der Spielstand `Session.state` sind Dictionaries. Welche Schlüssel es gibt, steht in [`docs/api.md`](api.md).
- Typisierte Dictionaries gehen auch: `var points: Dictionary[String, int] = {}`.

### Achtung: Arrays und Dictionaries sind Referenzen

Anders als in PHP werden Arrays und Dictionaries **nicht kopiert**, wenn man sie weitergibt oder einer anderen Variable zuweist:

```gdscript
var a := [1, 2]
var b := a
b.append(3)
print(a)          # [1, 2, 3] – a hat sich mitverändert!

var c := a.duplicate()        # flache Kopie
var d := state.duplicate(true)  # tiefe Kopie (auch verschachtelte Arrays/Dictionaries)
```

Wer `Session.state` in einem Bildschirm verändert, verändert also den Stand für alle. **Den Stand vom Server nur lesen**, nie direkt ändern.

## 8. Operatoren

| Operator | Bedeutung | Beispiel |
|---|---|---|
| `+ - * /` | Rechnen | |
| `/` mit zwei `int` | **Ganzzahlige Division!** | `7 / 2` = `3`, `7 / 2.0` = `3.5` |
| `%` | Rest (nur `int`; für `float`: `fmod()`) | `7 % 3` = `1` |
| `**` | Potenz | `2 ** 3` = `8` |
| `+= -= *= /=` | Rechnen und zuweisen | `score += 2` |
| `== !=` | Gleich / ungleich (`1 == 1.0` ist `true`) | |
| `< > <= >=` | Vergleich | |
| `and or not` | Logik. **Bevorzugt** (Style Guide) | `if not res.get("ok", false):` |
| `&& \|\| !` | Gleiche Bedeutung, andere Schreibweise | |
| `in` / `not in` | Enthalten in Array, Dictionary (Schlüssel) oder String | `"B" in letters` |
| `is` / `is not` | Typprüfung | `node is Button` |
| `as` | Typumwandlung | `node as Button` |
| `x if bedingung else y` | Bedingter Ausdruck (wie `? :` in PHP) | `"Host" if is_host() else "Gast"` |
| `&` `\|` `^` `~` `<<` `>>` | Bit-Operationen | selten nötig |

Es gibt **kein `++`/`--`**. Stattdessen `count += 1`.
Es gibt **kein `===`**. `==` vergleicht bei unterschiedlichen Typen meist mit Fehler oder `false` (Ausnahme: `int` und `float`).

Nützliche Rechenfunktionen: `wrapi(wert, min, max)` (springt am Ende wieder an den Anfang, benutzen wir beim Avatar-Wechsel), `clampi`/`clampf` (begrenzen), `mini`/`maxi`, `absi`, `roundi`, `floori`, `randi_range(1, 6)`, `randf()`. Die Endung **`i`** steht für die `int`-Variante, **`f`** für die `float`-Variante.

## 9. Kontrollstrukturen

### if

```gdscript
if count < 2:
	hint = "Zu wenige Spieler"
elif count > 8:         # elif, nicht "else if"
	hint = "Zu viele"
else:
	hint = "Los geht's"
```

### Schleifen

```gdscript
for player in players:          # über ein Array
	print(player["name"])

for player: Dictionary in state.get("players", []):   # mit Typ der Schleifenvariable
	pass

for i in range(5):              # 0, 1, 2, 3, 4
	pass
for i in range(2, 10, 2):       # 2, 4, 6, 8
	pass
for i in 5:                     # Kurzform für range(5)
	pass

for key in dict:                # über die Schlüssel eines Dictionaries
	pass

while not done:
	pass
```

`break` beendet die Schleife, `continue` springt zum nächsten Durchlauf.

### match

```gdscript
match state["lobby"]["phase"]:
	"lobby":
		Screens.goto(Screens.LOBBY)
	"playing", "word_choice":       # mehrere Werte
		Screens.goto(Screens.GAME)
	"results":
		Screens.goto(Screens.RESULTS)
	_:                              # alles andere (wie default)
		push_warning("Unbekannte Phase")
```

`match` kann auch Muster prüfen, z. B. Arrays (`[1, _]`) oder Dictionaries (`{"ok": true, ..}`), und Werte binden (`var x`). Siehe Godot-Doku unter „match“.

## 10. Funktionen

```gdscript
func join_lobby(code: String) -> bool:
	# ...
	return true


func greet(name: String, greeting := "Hallo") -> String:   # Standardwert, Typ wird abgeleitet
	return "%s, %s!" % [greeting, name]


func _show_avatar() -> void:        # gibt nichts zurück
	%AvatarLabel.text = "Avatar %d" % (Session.avatar + 1)
```

- **`-> Typ`** gibt den Rückgabetyp an. **`-> void`** heißt: gibt nichts zurück.
- Wir geben bei **jedem Parameter und jeder Funktion** den Typ an.
- Parameter, die man nicht benutzt, bekommen ein `_` davor, sonst gibt es eine Warnung: `func _process(_delta: float) -> void:`
- Es gibt **keine benannten Argumente**. Die Reihenfolge zählt.
- Variadisch (beliebig viele Argumente): `func log_all(...args: Array) -> void:`

### Statische Funktionen

```gdscript
static func format_code(code: String) -> String:
	return code.strip_edges().to_upper()
```

Aufruf über den Klassennamen (`MyClass.format_code("k7qx")`), ohne Objekt. Kein Zugriff auf Eigenschaften des Knotens.

### Funktionen als Werte: Callables und Lambdas

Eine Funktion ohne `()` ist ein **Callable**, also ein Wert, den man weitergeben und später aufrufen kann:

```gdscript
%CreateButton.pressed.connect(_on_create_pressed)      # Funktion als Wert übergeben
var f := _on_create_pressed
f.call()                                               # später aufrufen
```

**`bind()`** hängt feste Argumente an:

```gdscript
%PrevAvatar.pressed.connect(_change_avatar.bind(-1))   # beim Klick: _change_avatar(-1)
%NextAvatar.pressed.connect(_change_avatar.bind(1))    # beim Klick: _change_avatar(1)
```

**Lambda** (anonyme Funktion), praktisch für kurze Dinge:

```gdscript
%BackButton.pressed.connect(func() -> void: Screens.goto(Screens.LOBBY))

var double := func(x: int) -> int: return x * 2
double.call(4)   # 8

# mehrzeilig:
var on_error := func(message: String) -> void:
	%HintLabel.text = message
	%HintLabel.modulate = Color.RED
```

Achtung: Lambdas bekommen eine **Kopie** der lokalen Variablen von außen (Stand beim Erstellen). Ändert die Lambda eine solche Variable, ändert sich draußen nichts. Eigenschaften des Knotens (Variablen oben im Skript) und Inhalte von Arrays/Dictionaries kann sie dagegen ganz normal ändern.

### call_deferred

```gdscript
get_tree().change_scene_to_file.call_deferred(scene_path)
```

`call_deferred` führt den Aufruf erst **am Ende des aktuellen Frames** aus. Das braucht man, wenn man etwas tun will, während Godot gerade selbst mit dem Szenenbaum beschäftigt ist (Szene wechseln, Knoten löschen oder hinzufügen aus einem Signal heraus).

## 11. Knoten und der Szenenbaum

### Auf Knoten zugreifen

```gdscript
$Panel/NameEdit          # Pfad relativ zum eigenen Knoten (Kurzform für get_node("Panel/NameEdit"))
$"Panel/Name Edit"       # mit Leerzeichen: Pfad in Anführungszeichen
%NameEdit                # "Unique Name": egal, wo der Knoten in der Szene steckt
get_parent()             # Elternknoten
get_children()           # alle Kinder
get_node_or_null("X")    # null statt Fehler, wenn es ihn nicht gibt
get_tree()               # der ganze Szenenbaum
```

**Wir benutzen `%Name`.** Dafür im Szenen-Dock Rechtsklick auf den Knoten → *Access as Unique Name* (in der deutschen Oberfläche sinngemäß „Als eindeutigen Namen verwenden“). Dann erscheint ein `%` neben dem Knoten. Vorteil: Wenn jemand die Szene umbaut und den Knoten verschiebt, funktioniert das Skript trotzdem noch. Mit `$Pfad/zum/Knoten` bricht es.

Wer einen Knoten oft braucht, speichert ihn in einer Variable:

```gdscript
@onready var _status: Label = %StatusLabel
```

`@onready` heißt: erst zuweisen, wenn der Knoten bereit ist (siehe unten). Ohne `@onready` wäre der Knoten zu dem Zeitpunkt noch nicht da.

### Lebenszyklus: Funktionen, die Godot selbst aufruft

Diese Funktionen beginnen mit `_` und werden **nie selbst aufgerufen**, sondern von Godot:

| Funktion | Wann |
|---|---|
| `_init()` | Beim Erzeugen des Objekts (noch nicht im Baum, Kinder noch nicht verfügbar) |
| `_enter_tree()` | Knoten wurde in den Szenenbaum eingefügt |
| `_ready()` | Knoten **und alle seine Kinder** sind fertig. **Hier** Signale verbinden und Anfangswerte setzen |
| `_process(delta)` | Jeden Frame. `delta` = Sekunden seit dem letzten Frame |
| `_physics_process(delta)` | In festen Abständen (für Physik) |
| `_input(event)` | Bei jeder Eingabe (Taste, Maus, Touch) |
| `_unhandled_input(event)` | Bei Eingaben, die kein UI-Element verarbeitet hat. Gut für Tastatur-Shortcuts |
| `_exit_tree()` | Knoten wird aus dem Baum entfernt. Hier aufräumen |

Reihenfolge von `_ready()`: **Kinder zuerst, dann die Eltern.** Ein Eltern-Skript kann sich also in `_ready()` darauf verlassen, dass alle Kinder bereit sind.

Unser Spiel braucht `_process` kaum. Es reagiert auf Signale (Klicks, neuer Spielstand), statt jeden Frame etwas zu prüfen. Das ist sparsamer und übersichtlicher.

### Knoten erzeugen und löschen

```gdscript
var button := Button.new()          # Knoten im Code erzeugen
button.text = "A"
add_child(button)                   # in den Baum hängen (erst dann sichtbar/aktiv)

const AVATAR := preload("res://components/avatar/avatar.tscn")
var avatar: Node = AVATAR.instantiate()   # Szene als Vorlage benutzen
add_child(avatar)

button.queue_free()                 # löschen (am Ende des Frames, sicher)
is_instance_valid(button)           # false, sobald er gelöscht ist
```

- **`queue_free()` statt `free()`**: `free()` löscht sofort und führt zu Abstürzen, wenn noch jemand den Knoten benutzt.
- **`preload()`** lädt beim Start des Skripts (Pfad muss fest sein). **`load()`** lädt erst beim Aufruf (Pfad darf eine Variable sein).

### Bildschirm wechseln

Bei uns nie direkt `get_tree().change_scene_to_file(...)` aufrufen, sondern:

```gdscript
Screens.goto(Screens.LOBBY)
```

Beim Szenenwechsel wird die alte Szene mit allen Knoten **gelöscht**. Was erhalten bleiben soll (eigener Name, Token, Spielstand), gehört deshalb in `Session`.

## 12. Signale

Signale sind die Art, wie Knoten in Godot miteinander reden, ohne sich gegenseitig zu kennen.

### Eigenes Signal definieren und senden

```gdscript
signal state_changed(state: Dictionary)      # definieren (oben im Skript)
signal error_occurred(message: String)

state_changed.emit(state)                     # senden
error_occurred.emit("Verbindung verloren.")
```

### Auf ein Signal hören (verbinden)

```gdscript
func _ready() -> void:
	%StartButton.pressed.connect(_on_start_pressed)           # eingebautes Signal eines Buttons
	Session.state_changed.connect(_show_state)                # unser eigenes Signal
	%NameEdit.text_submitted.connect(func(_text: String) -> void: _on_create_pressed())


func _show_state(state: Dictionary) -> void:                  # Parameter passen zum Signal
	pass
```

- Die verbundene Funktion muss **dieselben Parameter** haben wie das Signal (oder man gleicht es mit `bind()`/`unbind()` an).
- Wird ein Knoten gelöscht, trennt Godot automatisch alle Verbindungen zu seinen Funktionen (auch zu Lambdas, die in ihm erstellt wurden). Wenn sich ein Bildschirm mit `Session.state_changed` verbindet, muss man beim Bildschirmwechsel also nichts selbst trennen.
- Weitere Methoden: `disconnect(funktion)`, `is_connected(funktion)`, `connect(funktion, CONNECT_ONE_SHOT)` (nur einmal auslösen).

### Im Editor verbinden

Man kann Signale auch im Editor verbinden (Knoten auswählen → Reiter *Node/Knoten* → *Signale* → Doppelklick). Godot legt dann eine Funktion `_on_<Knotenname>_<signal>()` an, z. B. `_on_start_button_pressed()`. **Wir verbinden im Code** (in `_ready()`), weil man dann im Skript sieht, was passiert, und es sich besser mergen lässt.

### Häufige eingebaute Signale

| Knoten | Signal | Wann |
|---|---|---|
| `Button` | `pressed` | Geklickt |
| `LineEdit` | `text_changed(text)` / `text_submitted(text)` | Text geändert / Enter gedrückt |
| `ItemList` | `item_selected(index)` | Eintrag gewählt |
| `Timer` | `timeout` | Zeit abgelaufen |
| `HTTPRequest` | `request_completed(result, code, headers, body)` | Antwort da |
| `Node` | `tree_exited`, `ready` | Aus dem Baum entfernt, bereit |

### Signal nach oben, Aufruf nach unten

Faustregel für unsere Komponenten: **Eltern rufen Funktionen ihrer Kinder auf, Kinder senden Signale nach oben.** Eine Tastatur-Komponente kennt den Spielbildschirm nicht, sie sendet nur `letter_pressed("A")`. Der Spielbildschirm hört darauf und schickt den Buchstaben an den Server. So kann man Komponenten einzeln bauen und testen.

## 13. `await`: auf etwas warten

`await` pausiert die Funktion, bis ein Signal kommt oder eine andere Funktion fertig ist. Der Rest des Spiels läuft währenddessen weiter (die Oberfläche friert nicht ein).

```gdscript
var result: Array = await http.request_completed       # auf ein Signal warten
await get_tree().create_timer(2.0).timeout             # 2 Sekunden warten

var res: Dictionary = await Api.call_action("ping")    # auf eine Funktion warten, die selbst await benutzt
if await Session.create_lobby():
	Screens.goto(Screens.LOBBY)
```

- **Enthält eine Funktion `await`, muss man sie mit `await` aufrufen**, wenn man ihr Ergebnis braucht. Ohne `await` bekommt man nicht das Ergebnis, und die Funktion läuft im Hintergrund weiter.
- Ohne `await` aufrufen ist nur okay, wenn das Ergebnis egal ist (so in `Session.leave_lobby()`).
- Während gewartet wird, kann der Spieler weiterklicken. Deshalb sperren wir Buttons, solange eine Anfrage läuft (`_set_busy(true)` im Startbildschirm), und `Session` merkt sich mit `_poll_running`, ob noch eine Abfrage unterwegs ist.
- Nach einem `await` kann sich die Welt geändert haben: Inzwischen kann ein neuer Spielstand angekommen oder der Bildschirm gewechselt sein. Werte, die man vor dem `await` gelesen hat, sind danach vielleicht veraltet. Den Bildschirm wechselt man deshalb erst **nach** dem `await`, so wie im Startbildschirm.

## 14. Annotations (`@`)

Annotations beginnen mit `@` und geben Godot Zusatzinfos zu Variablen, Funktionen oder dem ganzen Skript.

| Annotation | Bedeutung |
|---|---|
| `@export var speed := 100` | Variable im **Inspektor** einstellbar (pro Knoten unterschiedlich). Gut für Komponenten: Farbe, Größe, Anzahl |
| `@export_range(1, 8) var max_players := 8` | Mit Schieberegler und Grenzen |
| `@export_enum("Leicht", "Schwer") var level := 0` | Auswahlliste |
| `@export_multiline var text := ""` | Mehrzeiliges Textfeld |
| `@export_file("*.png") var image := ""` | Dateiauswahl |
| `@export_group("Aussehen")` | Gruppiert die folgenden Exporte im Inspektor |
| `@onready var x := %Label` | Erst zuweisen, wenn der Knoten bereit ist (direkt vor `_ready()`) |
| `@tool` | Skript läuft auch im Editor (für Vorschauen). Vorsicht, damit kann man den Editor kaputt machen |
| `@icon("res://icon.svg")` | Eigenes Symbol für eine Klasse |
| `@warning_ignore("unused_parameter")` | Eine bestimmte Warnung für die nächste Zeile/Funktion abschalten |
| `@static_unload` | Statische Variablen freigeben, wenn das Skript nicht mehr gebraucht wird |

## 15. Klassen, Enums, Setter und Getter

### `class_name`

```gdscript
class_name HangmanDrawing
extends Control
```

Damit ist `HangmanDrawing` überall als **Typ** bekannt: `var drawing: HangmanDrawing`, `HangmanDrawing.new()`, `if node is HangmanDrawing:`. Die Komponente erscheint dann auch in der Liste beim Hinzufügen neuer Knoten.

### Erben und `super`

```gdscript
extends Button

func _ready() -> void:
	super()                 # _ready() der Elternklasse aufrufen
	text = "OK"
```

`super.methode()` ruft eine bestimmte Methode der Elternklasse auf. `self` ist das aktuelle Objekt. Man braucht es selten, weil Eigenschaften und Methoden auch ohne `self.` gefunden werden.

### Innere Klassen

```gdscript
class Guess:
	var letter: String
	var player_id: String

	func _init(p_letter: String, p_player_id: String) -> void:
		letter = p_letter
		player_id = p_player_id

var g := Guess.new("A", "f65b")
```

### Enums

```gdscript
enum Phase { LOBBY, PLAYING, RESULTS }

var phase := Phase.LOBBY
if phase == Phase.PLAYING:
	pass
Phase.keys()     # ["LOBBY", "PLAYING", "RESULTS"]
```

Ein Enum ist intern eine Zahl (`LOBBY` = 0, `PLAYING` = 1, …). Der Server schickt uns die Phase als **Text** (`"lobby"`). Dann ist ein `match` auf den String meist einfacher.

### Setter und Getter

Code, der automatisch ausgeführt wird, wenn eine Variable gesetzt oder gelesen wird:

```gdscript
var mistakes := 0:
	set(value):
		mistakes = clampi(value, 0, 10)
		queue_redraw()          # Zeichnung bei jeder Änderung neu malen
	get:
		return mistakes
```

So kann eine Komponente sich selbst aktualisieren: `drawing.mistakes = 3` reicht, und das Bild passt sich an.

## 16. Autoloads: unsere drei Helfer

Eingetragen unter *Projekt → Projekteinstellungen → Globals → Autoload* (bzw. in `project.godot`). Sie sind von jedem Skript aus direkt erreichbar:

| Name | Datei | Aufgabe | Wichtigste Teile |
|---|---|---|---|
| `Api` | `autoload/api.gd` | Spricht mit dem PHP-Server | `await Api.call_action("aktion", {...})` → `Dictionary` mit `"ok"` |
| `Session` | `autoload/session.gd` | Eigener Spieler + aktueller Spielstand, fragt jede Sekunde nach | `Session.state`, `Session.player_id`, `Session.is_host()`, Signale `state_changed`, `error_occurred` |
| `Screens` | `autoload/screens.gd` | Wechselt den Bildschirm | `Screens.goto(Screens.GAME)` |

Typischer Ablauf in einem Bildschirm:

```gdscript
func _ready() -> void:
	Session.state_changed.connect(_show_state)   # bei jedem neuen Stand neu anzeigen
	_show_state(Session.state)                   # und einmal sofort


func _show_state(state: Dictionary) -> void:
	# nur anzeigen, was im state steht – nie selbst Spielregeln ausrechnen
	pass


func _on_letter_pressed(letter: String) -> void:
	var res: Dictionary = await Api.call_action(
		"guess_letter", {"token": Session.token, "letter": letter}
	)
	if not res.get("ok", false):
		%HintLabel.text = res.get("error", "Fehler")
	# Kein eigenes Update nötig: Session bekommt den neuen Stand beim nächsten Polling
```

**Wichtig:** Das Spiel entscheidet **nichts** selbst (ob ein Buchstabe richtig ist, wer dran ist, wer gewonnen hat). Es schickt nur Wünsche an den Server und zeigt an, was zurückkommt. Sonst könnte man schummeln, und die Spieler würden Unterschiedliches sehen.

## 17. Dateien, Pfade und Ressourcen

### Pfade

| Präfix | Bedeutung |
|---|---|
| `res://` | Projektordner (`game/`). Im exportierten Spiel **nur lesbar** |
| `user://` | Ordner für Daten des Spielers (Einstellungen, Spielstände). Beschreibbar, im Browser im Browser-Speicher |
| `uid://` | Eindeutige ID einer Datei. Bleibt gleich, auch wenn die Datei verschoben wird |

Immer `/` als Trenner benutzen, auch unter Windows.

### Dateiendungen

| Endung | Was | Committen? |
|---|---|---|
| `.gd` | GDScript | ja |
| `.gd.uid` | Eindeutige ID des Skripts | **ja**, sonst gehen Verweise kaputt |
| `.tscn` | Szene (Textformat) | ja, aber **nur eine Person gleichzeitig** daran arbeiten (schwer zu mergen) |
| `.tres` | Ressource im Textformat (Theme, Stil, eigene Daten) | ja |
| `.import` | Import-Einstellungen für Bilder/Sounds/Schriften | ja |
| `.png`, `.svg`, `.ogg`, `.wav`, `.ttf` | Assets | ja |
| `project.godot` | Projekteinstellungen | ja (lieber über den Editor ändern) |
| `export_presets.cfg` | Einstellungen für den Web-Export | ja |
| `.godot/` | Zwischenspeicher des Editors | **nein** (steht in `.gitignore`) |

### Ressourcen

Ressourcen sind Daten, die Godot verwaltet: Bilder (`Texture2D`), Schriften, Sounds, Themes, Szenen (`PackedScene`). Man lädt sie mit `preload()`/`load()` oder zieht sie im Inspektor in ein `@export`-Feld.
Ressourcen werden **geteilt**: Lädt man dieselbe Datei zweimal, bekommt man dasselbe Objekt. Wer sie verändern will, ohne andere zu beeinflussen: `.duplicate()`.

### Im Browser

Unser Spiel läuft als Web-Export. Einiges geht dort anders:

```gdscript
if OS.has_feature("web"):
	var url: Variant = JavaScriptBridge.eval("window.location.href")   # JavaScript im Browser ausführen
```

- `OS.has_feature("web")` prüft, ob wir im Browser laufen. Lokal im Editor ist es `false`.
- Die Zwischenablage (`DisplayServer.clipboard_set`) funktioniert im Browser nur nach einem Klick des Spielers.
- Ton spielt der Browser erst ab, nachdem der Spieler einmal auf die Seite geklickt hat.
- Zum Testen im Browser: im Editor oben rechts auf das Browser-Symbol (*Remote Deploy*) klicken. Dafür muss die API lokal laufen (`php -S localhost:8000 -t server`).

## 18. Präfixe, Suffixe und Sonderzeichen auf einen Blick

| Zeichen | Bedeutung | Beispiel |
|---|---|---|
| `_name` (Funktion) | Privat: nur innerhalb des Skripts benutzen | `_enter_lobby()`, `_poll()` |
| `_name` (Variable) | Privat | `_poll_timer` |
| `_ready`, `_process`, … | Von Godot aufgerufene Funktion („virtuell“) | |
| `_delta` (Parameter) | Parameter wird absichtlich nicht benutzt | `func _process(_delta: float)` |
| `_on_<knoten>_<signal>` | Funktion, die auf ein Signal reagiert | `_on_create_pressed()` |
| `$Pfad` | Knoten über Pfad holen | `$Panel/Label` |
| `%Name` | Knoten über eindeutigen Namen holen | `%StartButton` |
| `@` | Annotation | `@export`, `@onready` |
| `&"text"` | `StringName` | `&"pressed"` |
| `^"pfad"` | `NodePath` | `^"Panel/Label"` |
| `:=` | Variable mit abgeleitetem Typ | `var count := 0` |
| `: Typ` | Typangabe | `var name: String` |
| `-> Typ` | Rückgabetyp | `-> void` |
| `#` | Kommentar | |
| `##` | Doc-Kommentar | |
| `#region` / `#endregion` | Zuklappbarer Bereich | |
| `:` am Zeilenende | Ein Block beginnt | `if x:` |
| `\` am Zeilenende | Zeile geht weiter | |
| `res://`, `user://`, `uid://` | Pfad-Präfixe | |
| `..._i` / `..._f` (bei Funktionen) | int- bzw. float-Variante | `clampi`, `clampf` |
| `Packed…Array` | Speichersparendes Array eines festen Typs | `PackedStringArray` |
| `is_…`, `has_…`, `can_…` | Funktion/Variable liefert `bool` | `is_host()`, `has("x")` |
| `…_changed`, `…_pressed`, `…_occurred` | Signalnamen: was **passiert ist** (Vergangenheit) | `state_changed` |
| `…Button`, `…Label`, `…Edit`, `…List` | Knotennamen verraten den Typ | `CreateButton`, `StatusLabel`, `NameEdit` |
| `…_screen.tscn` / `…_screen.gd` | Ein ganzer Bildschirm | `lobby_screen.tscn` |

## 19. Namenskonventionen und Reihenfolge im Skript

Nach dem [offiziellen Style Guide](https://docs.godotengine.org/de/4.x/tutorials/scripting/gdscript/gdscript_styleguide.html), `gdlint` prüft das:

| Was | Schreibweise | Beispiel |
|---|---|---|
| Dateien und Ordner | `snake_case` | `lobby_screen.gd`, `autoload/` |
| `class_name`, Knoten in Szenen | `PascalCase` | `HangmanDrawing`, `StartButton` |
| Funktionen, Variablen, Signale | `snake_case` | `join_lobby`, `player_name`, `state_changed` |
| Private Funktionen/Variablen | `_snake_case` | `_apply_state`, `_poll_running` |
| Konstanten, Enum-Werte | `UPPER_SNAKE_CASE` | `POLL_INTERVAL`, `Phase.LOBBY` |
| Enum-Namen | `PascalCase` | `enum Phase` |

**Reihenfolge im Skript:**

```gdscript
@tool                       # 1. ggf. @tool
class_name MyComponent      # 2. class_name
extends Control             # 3. extends
## Beschreibung             # 4. Doc-Kommentar

signal finished             # 5. Signale
enum Mode { A, B }          # 6. Enums
const MAX := 8              # 7. Konstanten
@export var size := 10      # 8. @export-Variablen
var count := 0              # 9. öffentliche Variablen
var _cache := {}            # 10. private Variablen
@onready var _label := %Label   # 11. @onready-Variablen

func _init() -> void:       # 12. _init, _enter_tree, _ready, _process, … (Godot-Funktionen)
	pass

func do_something() -> void:    # 13. öffentliche Funktionen
	pass

func _helper() -> void:     # 14. private Funktionen
	pass
```

(Im Beispiel fehlen aus Platzgründen die zwei Leerzeilen zwischen den Funktionen, `gdformat` ergänzt sie.)

Namen im Code auf **Englisch**, Kommentare und Texte im Spiel auf **Deutsch**.

## 20. Debuggen

| Werkzeug | Wofür |
|---|---|
| `print("Wert: ", x)` | Ausgabe im Ausgabe-Fenster unten. Mehrere Argumente werden ohne Komma aneinandergehängt |
| `print_debug(x)` | Wie `print`, zeigt zusätzlich Datei und Zeile |
| `prints(a, b)` | Mit Leerzeichen dazwischen |
| `JSON.stringify(dict, "\t")` | Dictionary lesbar ausgeben: `print(JSON.stringify(Session.state, "\t"))` |
| `push_warning("…")` / `push_error("…")` | Gelbe/rote Meldung im Debugger-Reiter *Fehler*, mit Stelle im Code |
| `assert(bedingung, "Text")` | Hält an, wenn die Bedingung falsch ist (nur im Debug-Build) |
| `breakpoint` | Hält an dieser Stelle an |
| Haltepunkt (F9) | Programm hält an, man sieht alle Variablen im Debugger |
| *Remote*-Szenenbaum | Während das Spiel läuft: Im Szenen-Dock oben auf *Remote* klicken, dann sieht man alle Knoten live und kann Werte im Inspektor anschauen |
| Debugger → *Fehler* | Alle Warnungen und Fehler mit Datei und Zeile |

Fehlermeldungen lesen: `Invalid access to property or key 'host_id' on a base object of type 'Dictionary'` heißt: Der Schlüssel `host_id` existiert in diesem Dictionary nicht. Lösung: `.get("host_id", "")`.
`Invalid call. Nonexistent function 'foo' in base 'Nil'` heißt: Die Variable ist `null`. Meist ist ein Knotenpfad falsch oder der Knoten noch nicht bereit.

Wenn etwas mit dem Server nicht klappt, auch ins Terminal von `php -S` schauen. Dort stehen die PHP-Fehler.

## 21. Typische Stolperfallen

1. **Einrückung falsch** oder Tabs und Leerzeichen gemischt. Der Code gehört dann zum falschen Block oder lässt sich nicht laden.
2. **`:` am Ende von `if`/`for`/`func` vergessen.**
3. **`"Text" + zahl`** → Fehler. `str(zahl)` oder `"%d" % zahl` benutzen.
4. **`7 / 2` ist `3`**, weil beide Zahlen `int` sind. Für `3.5`: `7 / 2.0` oder `float(7) / 2`.
5. **Zahlen aus JSON sind `float`.** JSON kennt nur „Zahl“, und Godot macht beim Einlesen `float` daraus (`3` wird `3.0`). Bei Bedarf mit `int(...)` umwandeln, z. B. `int(player["avatar"])`. Vergleiche wie `3 == 3.0` funktionieren trotzdem.
6. **Fehlender Dictionary-Schlüssel** → Fehler. `.get("key", standard)` benutzen.
7. **`:=` mit einem `Variant`** → „Cannot infer the type“. Passiert z. B. bei `var x := dict["key"]`. Lösung: Typ ausdrücklich angeben: `var x: String = dict["key"]`.
8. **Knoten in `_init()` oder in Variablen ohne `@onready` holen** → `null`, weil die Kinder noch nicht da sind. In `_ready()` oder mit `@onready`.
9. **`await` vergessen** → man bekommt nicht das Ergebnis, sondern die Funktion läuft einfach weiter.
10. **Arrays/Dictionaries sind Referenzen.** Wer `Session.state` ändert, ändert ihn für alle. `.duplicate()` benutzen, wenn man eine Kopie braucht.
11. **Mit `free()` gelöscht** statt `queue_free()` → Absturz.
12. **Signal zweimal verbunden** (z. B. weil `_ready()` mehrmals läuft) → Funktion wird doppelt aufgerufen. Fehlermeldung: „Signal … is already connected“.
13. **Godot-3-Code aus dem Internet** übernommen (`yield`, `export`, `onready`, `connect("signal", self, "methode")`, `instance()` statt `instantiate()`).
14. **Gleichzeitig dieselbe `.tscn` bearbeitet** → Merge-Konflikt, der schwer zu lösen ist. Vorher absprechen.
15. **`.uid`-Datei nicht committet** → bei anderen fehlen Verweise.
16. **Spiellogik im Spiel ausgerechnet** statt auf dem Server. Alles, was über richtig/falsch/Punkte/Reihenfolge entscheidet, gehört in PHP.

## 22. Rezept: einen neuen Bildschirm oder eine Komponente bauen

**Komponente** (z. B. Bildschirm-Tastatur) in `game/components/keyboard/`:

1. Neue Szene anlegen, oberster Knoten passend wählen (z. B. `GridContainer`), speichern als `components/keyboard/keyboard.tscn`.
2. Skript anhängen (`keyboard.gd`) mit `class_name Keyboard`.
3. Einstellbares mit `@export`, Ereignisse als `signal` (`letter_pressed(letter: String)`), Steuerung von außen über öffentliche Funktionen (`disable_letter("A")`).
4. **Die Komponente kennt weder `Api` noch `Session`.** Sie bekommt Daten von außen und meldet sich per Signal. Dann kann man sie mit **F6** allein testen.
5. Im Bildschirm einbauen: Szene in den Szenenbaum ziehen, als `%Keyboard` markieren, in `_ready()` verbinden:
   ```gdscript
   %Keyboard.letter_pressed.connect(_on_letter_pressed)
   ```

**Bildschirm** (z. B. Spielbildschirm):

1. Szene in `game/screens/<name>/<name>_screen.tscn`, Skript `<name>_screen.gd` mit `extends Control`.
2. Falls neu: Konstante in `autoload/screens.gd` eintragen.
3. In `_ready()`: Signale verbinden, `Session.state_changed.connect(_show_state)`, `_show_state(Session.state)`.
4. `_show_state()` zeigt **nur** an, was im Stand steht. Aktionen gehen über `Api.call_action(...)`.
5. Vor dem Commit: `gdformat game/` und `gdlint game/`, Spiel einmal mit zwei Instanzen ausprobieren.

## 23. Unsere Regeln (Kurzfassung)

- Godot **4.7**, Standard-Version (nicht .NET).
- Tabs zum Einrücken, höchstens 100 Zeichen pro Zeile.
- Immer Typen angeben: `var x: int`, `var x := 0`, `func f(a: String) -> void`.
- Knoten über `%UniqueName` ansprechen, Signale im Code in `_ready()` verbinden.
- Private Funktionen und Variablen mit `_` beginnen.
- Server-Anfragen nur über `Api.call_action`, Spielstand nur aus `Session.state`, Bildschirmwechsel nur über `Screens.goto`.
- Keine Spielregeln im Spiel, die entscheidet der Server.
- Komponenten senden Signale nach oben und kennen `Api`/`Session` nicht.
- An einer `.tscn` arbeitet immer nur eine Person.
- Vor dem Commit: `gdformat game/` und `gdlint game/`. `.uid`-Dateien mit committen.
- Namen auf Englisch, Kommentare und Texte im Spiel auf Deutsch.

## 24. Zum Weiterlesen

- [GDScript-Referenz](https://docs.godotengine.org/de/4.x/tutorials/scripting/gdscript/gdscript_basics.html): die vollständige Sprachbeschreibung
- [GDScript Style Guide](https://docs.godotengine.org/de/4.x/tutorials/scripting/gdscript/gdscript_styleguide.html)
- [Statische Typen in GDScript](https://docs.godotengine.org/de/4.x/tutorials/scripting/gdscript/static_typing.html)
- [Signale verwenden](https://docs.godotengine.org/de/4.x/getting_started/step_by_step/signals.html)
- [UI-Tutorials (Control-Knoten, Container, Themes)](https://docs.godotengine.org/de/4.x/tutorials/ui/index.html): wichtig für unsere Bildschirme
- In Godot selbst: **F1** öffnet die Hilfe zu jeder Klasse, mit allen Eigenschaften, Methoden und Signalen.
- Unsere Dateien zum Nachlesen: [`autoload/api.gd`](../game/autoload/api.gd) → [`autoload/session.gd`](../game/autoload/session.gd) → [`screens/start/start_screen.gd`](../game/screens/start/start_screen.gd)
