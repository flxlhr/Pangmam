extends Control
## Startbildschirm: Name und Avatar wählen, Lobby erstellen oder beitreten.
## Platzhalter-Layout – das echte Design (Avatar-Rad usw.) kommt über die Issues.


func _ready() -> void:
	%NameEdit.text = Session.player_name
	%CodeEdit.text = Session.lobby_code_from_url()
	_show_avatar()
	%PrevAvatar.pressed.connect(_change_avatar.bind(-1))
	%NextAvatar.pressed.connect(_change_avatar.bind(1))
	%CreateButton.pressed.connect(_on_create_pressed)
	%JoinButton.pressed.connect(_on_join_pressed)
	Session.error_occurred.connect(_show_status)


func _change_avatar(step: int) -> void:
	Session.avatar = wrapi(Session.avatar + step, 0, Session.AVATAR_COUNT)
	_show_avatar()


func _show_avatar() -> void:
	%AvatarLabel.text = "Avatar %d" % (Session.avatar + 1)


func _on_create_pressed() -> void:
	if not _read_name():
		return
	_set_busy(true)
	if await Session.create_lobby():
		Screens.goto(Screens.LOBBY)
	_set_busy(false)


func _on_join_pressed() -> void:
	if not _read_name():
		return
	if %CodeEdit.text.strip_edges() == "":
		_show_status("Bitte einen Lobby-Code eingeben.")
		return
	_set_busy(true)
	if await Session.join_lobby(%CodeEdit.text):
		Screens.goto(Screens.LOBBY)
	_set_busy(false)


func _read_name() -> bool:
	Session.player_name = %NameEdit.text.strip_edges()
	if Session.player_name == "":
		_show_status("Bitte gib zuerst einen Namen ein.")
		return false
	return true


func _set_busy(busy: bool) -> void:
	%CreateButton.disabled = busy
	%JoinButton.disabled = busy
	if busy:
		_show_status("Verbinde …")


func _show_status(message: String) -> void:
	%StatusLabel.text = message
