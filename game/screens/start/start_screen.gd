extends Control
## Startbildschirm: Name und Avatar wählen, Lobby erstellen oder beitreten.
## Platzhalter-Layout – das echte Design (Avatar-Rad usw.) kommt über die Issues.
##
## Hinweis: %Name greift auf einen Knoten der Szene zu, der in Godot als
## „Unique Name“ markiert ist (egal, wo er im Szenenbaum steckt).


func _ready() -> void:
	# Felder vorbelegen: zuletzt benutzter Name und ggf. Lobby-Code aus dem Einladungslink.
	%NameEdit.text = Session.player_name
	%CodeEdit.text = Session.lobby_code_from_url()
	_show_avatar()
	# Buttons mit ihren Funktionen verbinden. bind(-1)/bind(1) gibt die Richtung mit.
	%PrevAvatar.pressed.connect(_change_avatar.bind(-1))
	%NextAvatar.pressed.connect(_change_avatar.bind(1))
	%CreateButton.pressed.connect(_on_create_pressed)
	%JoinButton.pressed.connect(_on_join_pressed)
	# Fehler vom Server (z. B. „Lobby ist voll“) direkt im Statustext anzeigen.
	Session.error_occurred.connect(_show_status)


## Blättert zum vorigen (step = -1) oder nächsten (step = 1) Avatar.
func _change_avatar(step: int) -> void:
	# wrapi sorgt dafür, dass nach dem letzten Avatar wieder der erste kommt (und umgekehrt).
	Session.avatar = wrapi(Session.avatar + step, 0, Session.AVATAR_COUNT)
	_show_avatar()


## Zeigt den aktuell gewählten Avatar an (vorerst nur als Text, z. B. „Avatar 3“).
func _show_avatar() -> void:
	%AvatarLabel.text = "Avatar %d" % (Session.avatar + 1)


## „Lobby erstellen“ gedrückt.
func _on_create_pressed() -> void:
	if not _read_name():
		return
	# Buttons sperren, damit man nicht doppelt klickt, während auf den Server gewartet wird.
	_set_busy(true)
	if await Session.create_lobby():
		Screens.goto(Screens.LOBBY)
	_set_busy(false)


## „Beitreten“ gedrückt.
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


## Übernimmt den eingegebenen Namen in die Session. Gibt false zurück, wenn er leer ist.
func _read_name() -> bool:
	Session.player_name = %NameEdit.text.strip_edges()
	if Session.player_name == "":
		_show_status("Bitte gib zuerst einen Namen ein.")
		return false
	return true


## Sperrt (busy = true) oder entsperrt die Buttons während einer Server-Anfrage.
func _set_busy(busy: bool) -> void:
	%CreateButton.disabled = busy
	%JoinButton.disabled = busy
	if busy:
		_show_status("Verbinde …")


## Zeigt einen Hinweis- oder Fehlertext unter den Buttons an.
func _show_status(message: String) -> void:
	%StatusLabel.text = message
