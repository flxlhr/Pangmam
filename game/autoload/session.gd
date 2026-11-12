extends Node
## Alles, was der eigene Spieler über seine Lobby weiß.
##
## Der Server entscheidet über den Spielstand. Session fragt ihn jede Sekunde,
## ob sich etwas geändert hat (Polling), und meldet Änderungen per Signal.
## Bildschirme lesen nur Session.state und hören auf state_changed.

signal state_changed(state: Dictionary)
signal error_occurred(message: String)

const POLL_INTERVAL := 1.0
const AVATAR_COUNT := 12

var player_name := ""
var avatar := 0
var player_id := ""
var token := ""
var lobby_code := ""
## Letzter bekannter Spielstand vom Server (Format: docs/api.md).
var state: Dictionary = {}

var _poll_timer := Timer.new()
var _poll_running := false


func _ready() -> void:
	_poll_timer.wait_time = POLL_INTERVAL
	_poll_timer.timeout.connect(_poll)
	add_child(_poll_timer)


func create_lobby() -> bool:
	var res: Dictionary = await Api.call_action(
		"create_lobby", {"name": player_name, "avatar": avatar}
	)
	return _enter_lobby(res)


func join_lobby(code: String) -> bool:
	var res: Dictionary = await Api.call_action(
		"join_lobby", {"code": code.strip_edges().to_upper(), "name": player_name, "avatar": avatar}
	)
	return _enter_lobby(res)


func leave_lobby() -> void:
	if token != "":
		Api.call_action("leave_lobby", {"token": token})
	_poll_timer.stop()
	player_id = ""
	token = ""
	lobby_code = ""
	state = {}


func is_host() -> bool:
	return state.get("lobby", {}).get("host_id", "") == player_id


## Link, den man an Freunde schickt. Im Browser die echte Adresse, lokal nur der Code.
func lobby_link() -> String:
	if OS.has_feature("web"):
		var page: Variant = JavaScriptBridge.eval(
			"window.location.origin + window.location.pathname"
		)
		return "%s?lobby=%s" % [str(page), lobby_code]
	return lobby_code


## Lobby-Code aus der Adresse lesen (…/pangmam/?lobby=K7QX). Leer, wenn keiner da ist.
func lobby_code_from_url() -> String:
	if not OS.has_feature("web"):
		return ""
	var code: Variant = JavaScriptBridge.eval(
		"new URLSearchParams(window.location.search).get('lobby') || ''"
	)
	return str(code).to_upper()


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


func _poll() -> void:
	if _poll_running or token == "":
		return
	_poll_running = true
	var res: Dictionary = await Api.call_action(
		"state", {"token": token, "since": state.get("version", 0)}
	)
	_poll_running = false
	if not res.get("ok", false):
		error_occurred.emit(res.get("error", "Verbindung verloren."))
	elif res.get("changed", false):
		_apply_state(res["state"])


func _apply_state(new_state: Dictionary) -> void:
	state = new_state
	state_changed.emit(state)
