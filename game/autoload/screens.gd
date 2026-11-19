extends Node
## Wechselt zwischen den vier Bildschirmen. Das Spiel bleibt dabei auf einer
## Browser-Seite – Godot tauscht nur die angezeigte Szene aus.
##
## Autoload: überall als "Screens" erreichbar, z. B. Screens.goto(Screens.LOBBY).

## Pfade zu den Szenen-Dateien der vier Bildschirme.
const START := "res://screens/start/start_screen.tscn"
const LOBBY := "res://screens/lobby/lobby_screen.tscn"
const GAME := "res://screens/game/game_screen.tscn"
const RESULTS := "res://screens/results/results_screen.tscn"


## Zeigt den Bildschirm aus scene_path an (eine der Konstanten oben).
func goto(scene_path: String) -> void:
	# call_deferred: Der Wechsel passiert erst am Ende des aktuellen Frames,
	# damit die alte Szene ihren gerade laufenden Code sauber beenden kann.
	get_tree().change_scene_to_file.call_deferred(scene_path)
