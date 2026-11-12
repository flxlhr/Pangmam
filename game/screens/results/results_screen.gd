extends Control
## Endstand nach dem letzten Durchgang, danach zurück in die Lobby.


func _ready() -> void:
	%BackButton.pressed.connect(func() -> void: Screens.goto(Screens.LOBBY))
