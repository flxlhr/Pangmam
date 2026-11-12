extends Control
## Lobby: Mitspieler sehen, Einladungslink teilen, (als Host) Spiel starten.
## Regeln einstellen und Starten sind noch offen – siehe Issues im Meilenstein M2/M3.


func _ready() -> void:
	%CodeLabel.text = "Lobby " + Session.lobby_code
	%CopyLinkButton.pressed.connect(_on_copy_link_pressed)
	%LeaveButton.pressed.connect(_on_leave_pressed)
	Session.state_changed.connect(_show_state)
	Session.error_occurred.connect(func(message: String) -> void: %HintLabel.text = message)
	_show_state(Session.state)


func _show_state(state: Dictionary) -> void:
	%PlayerList.clear()
	var host_id: String = state.get("lobby", {}).get("host_id", "")
	for player: Dictionary in state.get("players", []):
		var line: String = player["name"]
		if player["id"] == host_id:
			line += "  (Host)"
		if player["id"] == Session.player_id:
			line += "  ← du"
		if not player.get("connected", true):
			line += "  [weg?]"
		%PlayerList.add_item(line)

	var count: int = state.get("players", []).size()
	%StartButton.visible = Session.is_host()
	if Session.is_host():
		%HintLabel.text = "%d Spieler. Starten kommt in Meilenstein M2." % count
	else:
		%HintLabel.text = "%d Spieler. Warte auf den Host …" % count


func _on_copy_link_pressed() -> void:
	DisplayServer.clipboard_set(Session.lobby_link())
	%HintLabel.text = "Link kopiert: " + Session.lobby_link()


func _on_leave_pressed() -> void:
	Session.leave_lobby()
	Screens.goto(Screens.START)
