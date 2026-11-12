extends Node
## Wechselt zwischen den vier Bildschirmen. Das Spiel bleibt dabei auf einer
## Browser-Seite – Godot tauscht nur die angezeigte Szene aus.

const START := "res://screens/start/start_screen.tscn"
const LOBBY := "res://screens/lobby/lobby_screen.tscn"
const GAME := "res://screens/game/game_screen.tscn"
const RESULTS := "res://screens/results/results_screen.tscn"


func goto(scene_path: String) -> void:
	get_tree().change_scene_to_file.call_deferred(scene_path)
