extends Node
## Spricht mit der PHP-API (Ordner server/ im Repo).
##
## Lokal läuft die API unter http://localhost:8000/api/ (siehe README).
## Im Browser liegt sie neben dem Spiel: <Adresse des Spiels>/api/
## Jede Aktion ist ein POST mit JSON; die Antwort ist immer ein Dictionary
## mit "ok": true/false. Das Format steht in docs/api.md.

const LOCAL_BASE_URL := "http://localhost:8000/api/"

var base_url := LOCAL_BASE_URL


func _ready() -> void:
	if OS.has_feature("web"):
		# Ordner der aktuellen Seite, z. B. https://<domain>/
		var page_dir: Variant = JavaScriptBridge.eval(
			"window.location.origin + window.location.pathname.replace(/[^\\/]*$/, '')"
		)
		base_url = str(page_dir) + "api/"


## Ruft eine Aktion der API auf, z. B. call_action("create_lobby", {"name": "Felix"}).
## Bei Fehlern kommt {"ok": false, "error": "<Text für die Spieler>"} zurück.
func call_action(action: String, data: Dictionary = {}) -> Dictionary:
	var http := HTTPRequest.new()
	add_child(http)
	var url := base_url + "?action=" + action.uri_encode()
	var headers := PackedStringArray(["Content-Type: application/json"])
	var err := http.request(url, headers, HTTPClient.METHOD_POST, JSON.stringify(data))
	if err != OK:
		http.queue_free()
		return _error("Anfrage konnte nicht gesendet werden (%s)." % error_string(err))

	var result: Array = await http.request_completed
	http.queue_free()

	var request_result: int = result[0]
	var status: int = result[1]
	var body: PackedByteArray = result[3]
	if request_result != HTTPRequest.RESULT_SUCCESS:
		return _error("Server nicht erreichbar. Läuft die API? (%s)" % base_url)

	var parsed: Variant = JSON.parse_string(body.get_string_from_utf8())
	if typeof(parsed) != TYPE_DICTIONARY:
		return _error("Ungültige Antwort vom Server (HTTP %d)." % status)
	return parsed


func _error(message: String) -> Dictionary:
	push_warning("API: " + message)
	return {"ok": false, "error": message}
