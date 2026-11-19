extends Control
## Endstand nach dem letzten Durchgang, danach zurück in die Lobby.


func _ready() -> void:
	# „Zurück“-Button führt wieder in die Lobby (kurze Funktion direkt als Lambda).
	%BackButton.pressed.connect(func() -> void: Screens.goto(Screens.LOBBY))
