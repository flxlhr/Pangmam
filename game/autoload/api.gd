extends Node
## Spricht mit der PHP-API (Ordner server/ im Repo).
##
## Lokal läuft die API unter http://localhost:8000/api/ (siehe README).
## Im Browser liegt sie neben dem Spiel: <Adresse des Spiels>/api/
## Jede Aktion ist ein POST mit JSON; die Antwort ist immer ein Dictionary
## mit "ok": true/false. Das Format steht in docs/api.md.
##
## Autoload: Godot lädt dieses Skript beim Start automatisch. Es ist überall
## unter dem Namen "Api" erreichbar, z. B. Api.call_action("ping").

## Adresse der API, wenn das Spiel im Godot-Editor bzw. als Desktop-Programm läuft.
const LOCAL_BASE_URL := "http://localhost:8000/api/"

## Tatsächlich benutzte Adresse der API (wird im Browser in _ready() angepasst).
var base_url := LOCAL_BASE_URL


func _ready() -> void:
	# Läuft das Spiel im Browser (Web-Export)?
	if OS.has_feature("web"):
		# Ordner der aktuellen Seite, z. B. https://<domain>/
		# JavaScriptBridge führt JavaScript-Code im Browser aus: Adresse der Seite
		# nehmen und den Dateinamen am Ende (z. B. "index.html") abschneiden.
		var page_dir: Variant = JavaScriptBridge.eval(
			"window.location.origin + window.location.pathname.replace(/[^\\/]*$/, '')"
		)
		base_url = str(page_dir) + "api/"


## Ruft eine Aktion der API auf, z. B. call_action("create_lobby", {"name": "Felix"}).
## Bei Fehlern kommt {"ok": false, "error": "<Text für die Spieler>"} zurück.
## Muss mit "await" aufgerufen werden, weil auf die Antwort des Servers gewartet wird.
func call_action(action: String, data: Dictionary = {}) -> Dictionary:
	# Für jede Anfrage einen eigenen HTTPRequest-Knoten anlegen (er muss im Szenenbaum hängen).
	var http := HTTPRequest.new()
	add_child(http)
	# Aktion in die URL schreiben (uri_encode maskiert Sonderzeichen), Daten als JSON in den Body.
	var url := base_url + "?action=" + action.uri_encode()
	var headers := PackedStringArray(["Content-Type: application/json"])
	var err := http.request(url, headers, HTTPClient.METHOD_POST, JSON.stringify(data))
	if err != OK:
		# Anfrage konnte gar nicht erst losgeschickt werden.
		http.queue_free()
		return _error("Anfrage konnte nicht gesendet werden (%s)." % error_string(err))

	# Warten, bis der Server antwortet. Das Signal liefert:
	# [Ergebnis, HTTP-Status, Header, Body]
	var result: Array = await http.request_completed
	# Knoten wird nicht mehr gebraucht → löschen.
	http.queue_free()

	var request_result: int = result[0]
	var status: int = result[1]
	var body: PackedByteArray = result[3]
	# Keine Verbindung zum Server (z. B. API läuft nicht).
	if request_result != HTTPRequest.RESULT_SUCCESS:
		return _error("Server nicht erreichbar. Läuft die API? (%s)" % base_url)

	# Antwort von Bytes in Text umwandeln und als JSON lesen.
	var parsed: Variant = JSON.parse_string(body.get_string_from_utf8())
	# Kein gültiges JSON-Objekt (z. B. PHP-Fehlerseite) → Fehler melden.
	if typeof(parsed) != TYPE_DICTIONARY:
		return _error("Ungültige Antwort vom Server (HTTP %d)." % status)
	return parsed


## Baut eine Fehler-Antwort im selben Format wie die API und schreibt eine Warnung ins Godot-Log.
func _error(message: String) -> Dictionary:
	push_warning("API: " + message)
	return {"ok": false, "error": message}
