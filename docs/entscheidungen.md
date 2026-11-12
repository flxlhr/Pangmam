# Entscheidungen

Hier landet alles, was das Team entschieden hat – mit Begründung, damit man es später nachvollziehen kann.
Neue Einträge oben anfügen. Format: Datum, Entscheidung, Warum, Alternativen.

---

## 2026-11-12 · Abnahme des Lastenhefts v0.2 (Issue #7)

**Entscheidung**
- **Ganzes Wort raten ohne Abstimmung:** Wer das Wort zu wissen glaubt, gibt es einfach ein, jederzeit, auch wenn er gerade nicht an der Reihe ist. Richtig: 2 Punkte für den Rater, 1 für die übrigen Ratenden. Falsch: Der Rater ist für den Rest der Runde raus. Damit ersetzt diese Regel die Abstimmung aus dem zweiten Treffen, und die Frage nach einem Zeitlimit (offener Punkt 11) entfällt.
- **Gemeinsamer Vorschlag (Zusatzfunktion):** Ein Ratender kann ein ganzes Wort vorschlagen, dem alle anderen Ratenden zustimmen können. Ist es richtig, gewinnen alle normal (je 1 Punkt).
- **Beitreten während eines laufenden Spiels:** in Version 1 nicht möglich, kommt später als Soll-Anforderung (offener Punkt 12).
- **Grafik und Design:** Mara zeichnet die Avatare und übernimmt das Design insgesamt; Felix gibt Feedback.

**Folgen**
- Lastenheft angepasst: FA-35 geändert, FA-10 und FA-35a neu, Punktetabelle und offene Punkte 9–12 aktualisiert.
- Noch offen: was passiert, wenn ein gemeinsamer Vorschlag falsch ist (offener Punkt 14).

---

## 2026-11-12 · Eigene Webseite statt Unterseite von flxlhr.com

**Entscheidung**
- Pangmam bekommt eine **eigene Webseite mit eigener Domain** in einem **eigenen IONOS-Account**. Das Spiel liegt nicht mehr unter `flxlhr.com/pangmam/`.
- Spiel und API liegen direkt im Hauptordner der Domain: `https://<domain>/` und `https://<domain>/api/`.
- Die Domain steht noch nicht fest.

**Warum**
- Das Projekt soll unabhängig von Felix' privater Webseite sein.

**Folgen**
- Für Tarif und Domain fallen kleine monatliche Kosten an.
- Der Tarif muss **PHP 8.1+**, eine **MySQL-Datenbank** und **SFTP-Zugang** enthalten.
- Am Code ändert sich nichts. Das Spiel ermittelt die Adresse der API selbst aus der Adresse, unter der es aufgerufen wird.

---

## 2026-11-12 · Spiel in Godot, Server als PHP-API auf dem Webspace

**Entscheidung**
- Das Spiel wird mit **Godot 4.7** und **GDScript** gebaut und als **Web-Version** exportiert und läuft im Browser (Ort: siehe Eintrag „Eigene Webseite“ oben).
- Mehrspieler läuft über eine kleine **PHP-API mit MySQL** auf einem normalen IONOS-Webspace.
- Die Spiele fragen **jede Sekunde** nach, ob sich etwas geändert hat (Polling). Kein dauerhaft laufender Server, keine WebSockets.
- **Der Server entscheidet** über alles (Wort, Treffer, Punkte). Das Spiel zeigt nur an. So kann niemand das Wort auslesen.

**Warum**
- Hangman ist rundenbasiert. Eine Verzögerung von bis zu einer Sekunde stört nicht, daher braucht es keine Echtzeit-Verbindung.
- Ein normaler Webspace kann PHP ausführen, aber keine dauerhaft laufenden Programme. Polling funktioniert damit, ohne Zusatzkosten.
- GDScript statt C#: C#-Projekte lassen sich in Godot 4 nicht für den Browser exportieren. GDScript ist außerdem leichter zu lernen und hat die meisten Tutorials.

**Alternativen, die wir verworfen haben**
- *Eigener Godot-Server mit WebSockets:* bräuchte einen dauerhaft laufenden Server. Die kostenlosen Angebote (Oracle Always Free, Render Free, Koyeb) schlafen ein, verlangen eine Kreditkarte oder wurden eingeschränkt.
- *Peer-to-Peer (WebRTC), ein Spieler ist Host:* kostenlos, aber kompliziert, klappt nicht in jedem Netz, und das Spiel endet, wenn der Host geht.
- *HTML/CSS/JavaScript ohne Engine* (Vorschlag aus v0.1): verworfen zugunsten von Godot.

**Folgen**
- Das Team schreibt zwei Sprachen: GDScript (Spiel) und PHP (Server, eher klein).
- Lokal startet man die API mit `php -S localhost:8000 -t server` (SQLite statt MySQL).
- Alle nutzen die Standard-Version von Godot 4.7, nicht die .NET-Version.

---

## 2026-11-12 · Spielregeln aus dem zweiten Treffen

- Tastatur mit **Ä, Ö, Ü, ß**; die Spiele sind auf Deutsch.
- **Kein Smartphone-Layout**, nur Desktop/Tablet im Querformat.
- **Eine Browser-Seite**, die Bildschirme wechseln innerhalb des Spiels.
- **Host** stellt Regeln ein und startet.
- **10 Teile**, das Gestell ist vorgezeichnet.
- **Mindestens 2 Spieler.** Geht der Wortgeber, wird die Runde abgebrochen und es geht mit dem nächsten Wortgeber weiter; unter 2 Spielern zurück in die Lobby.
- **Ganzes Wort raten** mit Abstimmung der Gruppe; richtig = 2 Punkte für den Rater und 1 für die anderen, falsch = Rater ist für die Runde raus.
- **Gruppenmodus zuerst**, Einzelmodus „jeder gegen jeden“ später.
- **Avatare:** vorgegebene Tiere statt Baukasten.
- **Lobbys per Link/Code** statt Liste laufender Spiele.
- Repo ist **öffentlich**, Lizenz MIT. Abgabe: **28.01.2027**.
