extends Node
## Alles, was der eigene Spieler über seine Lobby weiß.
##
## Der Server entscheidet über den Spielstand. Session fragt ihn jede Sekunde,
## ob sich etwas geändert hat (Polling), und meldet Änderungen per Signal.
## Bildschirme lesen nur Session.state und hören auf state_changed.
##
## Autoload: überall als "Session" erreichbar.

## Wird gesendet, sobald ein neuer Spielstand vom Server da ist.
signal state_changed(state: Dictionary)
## Wird gesendet, wenn die API einen Fehler meldet (Text ist für die Spieler gedacht).
signal error_occurred(message: String)

## Abstand zwischen zwei Abfragen beim Server, in Sekunden.
const POLL_INTERVAL := 1.0
## Anzahl wählbarer Avatare – muss zu LobbyService::AVATAR_COUNT im Server passen.
const AVATAR_COUNT := 12

## Eigener Name und Avatar (werden auf dem Startbildschirm gewählt).
var player_name := ""
var avatar := 0
## Vom Server vergeben, sobald man in einer Lobby ist:
## öffentliche Spieler-ID, geheimes Token und Lobby-Code.
var player_id := ""
var token := ""
var lobby_code := ""
## Letzter bekannter Spielstand vom Server (Format: docs/api.md).
var state: Dictionary = {}

## Timer, der jede POLL_INTERVAL Sekunden _poll() auslöst.
var _poll_timer := Timer.new()
## Verhindert, dass eine neue Abfrage startet, solange die vorige noch läuft.
var _poll_running := false


func _ready() -> void:
	# Timer einrichten und in den Szenenbaum hängen (sonst läuft er nicht).
	# Gestartet wird er erst, wenn man einer Lobby beigetreten ist.
	_poll_timer.wait_time = POLL_INTERVAL
	_poll_timer.timeout.connect(_poll)
	add_child(_poll_timer)


## Erstellt eine neue Lobby mit dem eigenen Namen und Avatar.
## Gibt true zurück, wenn es geklappt hat (sonst kommt error_occurred).
func create_lobby() -> bool:
	var res: Dictionary = await Api.call_action(
		"create_lobby", {"name": player_name, "avatar": avatar}
	)
	return _enter_lobby(res)


## Tritt der Lobby mit dem angegebenen Code bei.
## Gibt true zurück, wenn es geklappt hat (sonst kommt error_occurred).
func join_lobby(code: String) -> bool:
	# Code bereinigen: Leerzeichen weg, Großbuchstaben.
	var res: Dictionary = await Api.call_action(
		"join_lobby", {"code": code.strip_edges().to_upper(), "name": player_name, "avatar": avatar}
	)
	return _enter_lobby(res)


## Verlässt die Lobby und setzt alle Lobby-Daten zurück. Name und Avatar bleiben erhalten.
func leave_lobby() -> void:
	if token != "":
		# Ohne await: Auf die Antwort des Servers muss nicht gewartet werden.
		Api.call_action("leave_lobby", {"token": token})
	_poll_timer.stop()
	player_id = ""
	token = ""
	lobby_code = ""
	state = {}


## Ist der eigene Spieler gerade Host der Lobby?
func is_host() -> bool:
	# .get(..., Standardwert) verhindert Fehler, falls state noch leer ist.
	return state.get("lobby", {}).get("host_id", "") == player_id


## Link, den man an Freunde schickt. Im Browser die echte Adresse, lokal nur der Code.
func lobby_link() -> String:
	if OS.has_feature("web"):
		# Aktuelle Adresse der Seite ohne ?… am Ende, per JavaScript aus dem Browser geholt.
		var page: Variant = JavaScriptBridge.eval(
			"window.location.origin + window.location.pathname"
		)
		return "%s?lobby=%s" % [str(page), lobby_code]
	return lobby_code


## Lobby-Code aus der Adresse lesen (https://<domain>/?lobby=K7QX). Leer, wenn keiner da ist.
func lobby_code_from_url() -> String:
	if not OS.has_feature("web"):
		return ""
	var code: Variant = JavaScriptBridge.eval(
		"new URLSearchParams(window.location.search).get('lobby') || ''"
	)
	return str(code).to_upper()


## Wertet die Antwort von create_lobby/join_lobby aus.
## Bei Erfolg: Daten merken und Polling starten. Bei Fehler: error_occurred senden.
func _enter_lobby(res: Dictionary) -> bool:
	if not res.get("ok", false):
		error_occurred.emit(res.get("error", "Unbekannter Fehler."))
		return false
	player_id = res["player_id"]
	token = res["token"]
	lobby_code = res["code"]
	_apply_state(res["state"])
	_poll_timer.start()
	return true


## Wird vom Timer jede Sekunde aufgerufen: fragt den Server nach dem neuen Spielstand.
func _poll() -> void:
	# Läuft noch eine Abfrage oder ist man in keiner Lobby → nichts tun.
	if _poll_running or token == "":
		return
	_poll_running = true
	# "since" = zuletzt bekannte Version; der Server schickt nur bei Änderungen den ganzen Stand.
	var res: Dictionary = await Api.call_action(
		"state", {"token": token, "since": state.get("version", 0)}
	)
	_poll_running = false
	if not res.get("ok", false):
		error_occurred.emit(res.get("error", "Verbindung verloren."))
	elif res.get("changed", false):
		_apply_state(res["state"])


## Speichert den neuen Spielstand und informiert alle Bildschirme per Signal.
func _apply_state(new_state: Dictionary) -> void:
	state = new_state
	state_changed.emit(state)
