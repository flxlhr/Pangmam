# Lastenheft Multiplayer-Hangman (Pangmam)

**Version 0.2** · Stand 12.11.2026 · Grundlage: Lastenheft v0.1 (05.11.2026) und zweites Teamtreffen

> Was das Spiel können soll – nicht, wie es gebaut wird. Prioritäten: **Muss** (Version 1), **Soll** (wenn Zeit reicht), **Kann** (Idee für später).
> Änderungen gegenüber v0.1 sind mit **(neu)** oder **(geändert)** markiert. Das Original-PDF v0.1 liegt nicht im Repo.

## 1. Grundlagen und Ziele

Das Team baut ein Hangman für bis zu 8 Spieler, das im Browser läuft. Ein Wortgeber tritt gegen alle übrigen Spieler an, die gemeinsam raten. Vorbild für Lobbys und Ablauf ist skribbl.io.

| | |
|---|---|
| **8** | Spieler höchstens pro Lobby, **2** mindestens (neu) |
| **4** | Bildschirme: Start, Lobby, Spiel, Endstand |
| **10** | Fehlversuche bis der Hangman komplett ist; das Gestell ist schon vorgezeichnet (geändert, vorher 11) |
| **1** | Punkt für den Sieg einer Runde, **2** für den, der das ganze Wort errät (neu) |

**Ziele**
- Mehrere Spieler treffen sich in einer Lobby und spielen online miteinander.
- Die Regeln bleiben sofort verständlich: Wer Hangman kennt, weiß, was zu tun ist.
- Schwierigkeit und Regeln lassen sich einstellen, ohne neue Spieler zu überfordern.
- Erst ein einfacher Prototyp, dann schrittweise Erweiterungen.
- Das Team lernt dabei Godot, GDScript und einfache Web-Server-Technik kennen (bisherige Erfahrung: vor allem Java).

**Zielgruppe:** kleine Gruppen bis 8 Personen, die ohne Installation im Browser spielen wollen – an Laptop, Desktop oder Tablet.

## 2. Spielablauf und Bildschirme

Eine Partie besteht aus mehreren Runden. In jeder Runde wählt ein Wortgeber ein Wort, das alle übrigen Spieler als Team erraten. Vorher erstellt ein Spieler eine Lobby, teilt den Link, stellt die Regeln ein und startet das Spiel; das System verteilt die Rollen.

| Bildschirm | Inhalt |
|---|---|
| **Start** | Name eingeben, Tier-Avatar wählen, Lobby erstellen oder per Link/Code beitreten (geändert) |
| **Lobby** | Mitspieler sehen, Einladungslink kopieren, Host stellt Regeln ein und startet |
| **Spiel** | Spieler als Figuren um eine Tafel, Hangman auf der Tafel, Wort als Striche, Bildschirm-Tastatur. Der Wortgeber sieht sein Wort oben und schaut zu. |
| **Endstand** | Punkte und Platzierung (Siegertreppchen), danach zurück zur Lobby |

Alle Bildschirme liegen auf **einer** Browser-Seite; es wird nur die Ansicht gewechselt (neu).

**Ablauf einer Runde:** Wortgeber wählt Wort → erscheint als Striche → Ratende wählen nacheinander Buchstaben → Treffer werden an allen Stellen eingetragen, Fehler bauen den Hangman um ein Teil weiter → Wort komplett: Ratende gewinnen / Hangman komplett: Wortgeber gewinnt → Punkte → nächste Runde oder Endstand.

## 3. Funktionale Anforderungen

### Lobby und Spieler

| Nr. | Anforderung | Prio |
|---|---|---|
| FA-01 | Spieler geben beim Start einen Namen ein (max. 16 Zeichen, in der Lobby eindeutig). | Muss |
| FA-02 | **(geändert)** Spieler wählen einen von mindestens 8 vorgegebenen **Tier-Avataren** über ein Auswahlrad mit Pfeilen. Kein Baukasten. | Soll |
| FA-03 | **(geändert)** Eine Lobby hat einen kurzen Code und einen **Einladungslink** (`https://<domain>/?lobby=CODE`). Wer den Link öffnet, landet direkt beim Beitreten. | Muss |
| FA-03a | **(neu)** Liste offener Lobbys bzw. „Zufälliger Lobby beitreten“ auf dem Startbildschirm. (Ersetzt die frühere Muss-Anforderung, laufende Spiele anzuzeigen.) | Kann |
| FA-04 | Ein Spieler erstellt eine Lobby, andere treten ihr bei. | Muss |
| FA-05 | Eine Lobby fasst höchstens 8 Spieler; die Obergrenze lässt sich als Regel niedriger einstellen. | Muss |
| FA-06 | **(geändert)** Nur der **Host** (wer die Lobby erstellt hat) stellt die Regeln ein und startet das Spiel. Verlässt der Host die Lobby, übernimmt der nächste Spieler. | Muss |
| FA-07 | **(neu)** Ein Spiel braucht mindestens 2 Spieler. | Muss |
| FA-08 | **(neu)** Verlässt der **Wortgeber** das Spiel, wird die Runde abgebrochen und die nächste beginnt mit einem neuen Wortgeber. Sind weniger als 2 Spieler übrig, geht es zurück in die Lobby. | Muss |
| FA-09 | **(neu)** Wer die Verbindung verliert, kann über denselben Browser wieder in seine laufende Partie einsteigen. | Kann |

### Rollen und Runden

| Nr. | Anforderung | Prio |
|---|---|---|
| FA-10 | Beim Spielstart teilt das System die Spieler in einen Wortgeber und die Ratenden ein. | Muss |
| FA-11 | Pro Runde gibt es genau einen Wortgeber. Die Rolle wechselt so, dass jeder mindestens einmal Wortgeber ist. | Muss |
| FA-12 | Die Zahl der Durchgänge wird vor dem Start festgelegt; ein Durchgang ist komplett, wenn jeder einmal Wortgeber war. | Muss |

### Wortwahl

| Nr. | Anforderung | Prio |
|---|---|---|
| FA-20 | Der Wortgeber denkt sich ein Wort aus und gibt es per Tastatur ein. | Muss |
| FA-21 | Alternativ wählt er aus Vorschlägen oder lässt ein Zufallswort aus einer Wortliste ziehen; wie oft neu gezogen werden darf, ist einstellbar. | Soll |
| FA-22 | Eingaben werden vereinheitlicht: Großbuchstaben, keine Unterscheidung von Groß- und Kleinschreibung. **(geändert)** Erlaubt sind A–Z, Ä, Ö, Ü, ß und Leerzeichen. | Muss |
| FA-23 | Das Wort wird als Striche angezeigt, ein Strich je Buchstabe. | Muss |
| FA-24 | Mehrere Wörter wie „Die Schöne und das Biest“ sind erlaubt; die Wortgrenze zeigt ein größerer Abstand. | Muss |
| FA-25 | Der Wortgeber sieht sein Wort während der Runde oben im Spielbildschirm. | Muss |

### Raten

| Nr. | Anforderung | Prio |
|---|---|---|
| FA-30 | **(geändert)** Ratende wählen Buchstaben per Klick auf eine Bildschirm-Tastatur mit **A–Z, Ä, Ö, Ü und ß**. | Muss |
| FA-31 | Die Ratenden kommen nacheinander an die Reihe; immer nur eine Person rät gleichzeitig. | Muss |
| FA-32 | Das System prüft den Buchstaben und trägt bei einem Treffer alle Vorkommen automatisch ein. | Muss |
| FA-33 | Kommt der Buchstabe nicht vor, baut das System das nächste Teil der Hangman-Grafik auf. | Muss |
| FA-34 | Gewählte Buchstaben werden gesperrt und markiert: zuerst weiß, Treffer grün, Fehlversuche grau. | Muss |
| FA-35 | **(geändert)** **Ganzes Wort raten:** Ein Ratender drückt „Ich weiß es“ und tippt das Wort ein. Die anderen Ratenden stimmen ab (dafür/dagegen), das Ergebnis sehen alle. Im Gruppenmodus gilt die Mehrheit. **Richtig:** Runde gewonnen, der Rater bekommt 2 Punkte, die übrigen Ratenden 1. **Falsch:** Der Rater ist für den Rest der Runde raus (schaut zu, bekommt keinen Punkt); die anderen spielen normal weiter, der Hangman wächst dadurch nicht. | Muss |
| FA-36 | Die Spieler verständigen sich im Spiel über einen Chat. | Soll |
| FA-37 | Der Wortgeber kann Tipps geben. | Kann |
| FA-38 | **(neu)** **Einzelmodus „jeder gegen jeden“:** Jeder deckt für sich auf und sammelt eigene Punkte. Kommt erst nach dem Gruppenmodus. | Kann |

### Rundenende und Endstand

| Nr. | Anforderung | Prio |
|---|---|---|
| FA-40 | Die Ratenden gewinnen gemeinsam, sobald das Wort vollständig erraten ist. | Muss |
| FA-41 | Der Wortgeber gewinnt, wenn die Hangman-Grafik vollständig ist, bevor das Wort erraten wurde. | Muss |
| FA-42 | Das System vergibt am Ende jeder Runde die Punkte (Abschnitt 4). | Muss |
| FA-43 | Nach der letzten Runde zeigt das System den Endstand mit Platzierung (Siegertreppchen). | Muss |
| FA-44 | Nach dem Endstand geht es zurück zur Lobby, damit ein neues Spiel beginnen kann. | Soll |

### Hangman-Grafik

| Nr. | Anforderung | Prio |
|---|---|---|
| FA-50 | Die Grafik baut sich bei jedem Fehlversuch um ein Teil auf. | Muss |
| FA-51 | **(geändert)** Das Gestell ist von Anfang an vorgezeichnet; der Hangman selbst hat **10 Teile** (z. B. Seil, Kopf, Körper, Arme und Beine aufgeteilt). | Muss |
| FA-52 | Zu jeder einstellbaren Versuchszahl gibt es eine passende Grafikvariante. | Soll |
| FA-53 | Alternative Darstellungen (Garten mit Blumen, Halloween, Variante ohne Galgen) ändern nur das Aussehen. | Kann |
| FA-54 | **(neu)** Kleine Animation, wenn der Hangman komplett ist. | Kann |

## 4. Regeln, Punkte und Darstellung

### Einstellbare Regeln (nur Host)

| Regel | Standard und Optionen | Prio |
|---|---|---|
| Standardeinstellungen | Alles vorbelegt; erweiterte Einstellungen klappen aus. | Muss |
| Maximale Spielerzahl | 2 bis 8 | Muss |
| Anzahl Durchgänge | vor dem Start einstellbar | Muss |
| Anzahl Versuche | Standard 10; weitere Stufen nur, wenn es passende Grafiken gibt | Soll |
| Wortauswahl | Standard: selbst ausdenken. Optional Vorschläge oder Zufallswort | Soll |
| Voreingestellte Modi | „Einfach“, „Profi“, „Speed“ | Soll |
| Spielmodus | Gruppe (Standard) oder jeder gegen jeden | Kann |
| Versuche nach Wortlänge, falsche Buchstaben erneut ratbar, Tipps, Geschwindigkeit als Punktefaktor | wie in v0.1 | Kann |

### Punkte

| Ausgang der Runde | Ratende | Wortgeber |
|---|---|---|
| Wort durch Buchstaben erraten | je 1 | 0 |
| Wort als Ganzes richtig geraten (FA-35) | Rater 2, übrige Ratende je 1 | 0 |
| Hangman vollständig | 0 | 1 |

Wer nach einem falschen Ganzwort-Versuch raus ist, bekommt in dieser Runde keinen Punkt. Endstand = Summe über alle Runden.

### Darstellung und Bedienung

| Thema | Festlegung |
|---|---|
| Optik (Idee) | Spieler sitzen als Figuren im Kreis um eine Tafel; Hangman in Kreide-Optik. |
| Avatare | Vorgegebene Tierfiguren, gern etwas überdreht und lustig. |
| Tastatur | Buchstaben weiß, Fehlversuche grau, Treffer grün; alles in Großbuchstaben. |
| Texteingabe | Nur für Name, eigenes Wort, ganzes Wort raten und Chat. |
| Geräte | **(geändert)** Laptop, Desktop, Tablet im Querformat (16:9 bzw. 4:3). **Kein eigenes Smartphone-Layout**; kann später ergänzt werden. |
| Sprache | Deutsch. Weitere Sprachen (eigene Tastaturen je Sprache) sind eine spätere Erweiterung. |

## 5. Nicht-funktionale Anforderungen und Rahmenbedingungen

| Nr. | Anforderung | Prio |
|---|---|---|
| NF-01 | **(geändert)** Das Spiel läuft im Browser ohne Installation. Es wird mit Godot gebaut und als Web-Version exportiert. | Muss |
| NF-02 | Mehrere Spieler spielen gleichzeitig über das Internet in einer Lobby und sehen denselben Spielstand (Verzögerung höchstens etwa 1 Sekunde). | Muss |
| NF-03 | Lobbys, Spielstände und Wortliste liegen auf dem Server. | Soll |
| NF-04 | Die Bedienung ist ohne Erklärung verständlich. | Muss |
| NF-05 | **(geändert)** Umsetzung: Godot 4.7 mit GDScript für das Spiel, PHP 8 mit MySQL für die Server-Schnittstelle. Details: [entscheidungen.md](entscheidungen.md). | Muss |
| NF-06 | **(geändert)** Das Spiel bekommt eine **eigene Webseite mit eigener Domain** in einem eigenen IONOS-Account, getrennt von Felix' privater Webseite. Der Tarif enthält PHP 8.1+, MySQL und SFTP. | Muss |
| NF-07 | Der Quellcode ist Open Source (MIT) in einem öffentlichen GitHub-Repository. | Muss |
| NF-08 | Der Code ist für alle Teammitglieder nachvollziehbar und erklärbar. | Soll |
| NF-09 | **(neu)** Das gesuchte Wort wird nur an den Wortgeber übertragen; Ratende können es auch technisch nicht auslesen. | Muss |
| NF-10 | **(neu)** Fertigstellung bis zur letzten Übung am 28.01.2027. | Muss |

## 6. Zusammenarbeit

| Thema | Vereinbarung |
|---|---|
| Code | Öffentliches Repo [flxlhr/Pangmam](https://github.com/flxlhr/Pangmam); Ablauf in [CONTRIBUTING.md](../CONTRIBUTING.md) |
| Planung | [GitHub Project](https://github.com/users/flxlhr/projects/1) mit Sprints von Donnerstag bis Mittwoch und Meilensteinen M0–M4 |
| Abstimmung | Treffen jeden Donnerstag; dazwischen Discord mit Channels je Thema |
| Rollen | Fünf Rollen, siehe [team.md](team.md) |
| Vorgehen | Erst Prototyp, dann erweitern; Designstände werden in `docs/design/` versioniert |
| KI | Darf helfen; alle müssen den Code verstehen und erklären können |

## 7. Später / außerhalb von Version 1

- Wortgeber zeichnet die Hangman-Teile selbst
- Modus ohne Wortgeber (Wort aus dem Duden)
- Punkte nach Geschwindigkeit
- Wer zuerst eine Idee hat, darf raten (statt fester Reihenfolge)
- Smartphone-Layout, weitere Sprachen
- Beschäftigung für den Wortgeber während der Runde (z. B. Zeichenfläche)

## 8. Offene Punkte

| Nr. | Frage | Stand |
|---|---|---|
| 1 | Umlaute | ✔ Ä, Ö, Ü, ß auf der Tastatur |
| 2 | Smartphone | ✔ Nein, nur Desktop/Tablet |
| 3 | Technik für Lobbys | ✔ PHP-API + Polling auf dem Webspace |
| 4 | Unterseiten oder eine Seite | ✔ Eine Seite |
| 5 | Wer stellt Regeln ein | ✔ Host |
| 6 | Ganzes Wort falsch geraten | ✔ Rater ist raus, kein Hangman-Teil (FA-35) |
| 7 | 11 oder 10 Teile | ✔ 10, Gestell vorgezeichnet |
| 8 | Mindestspieler / Spieler geht | ✔ min. 2; FA-08 |
| 9 | Zustimmung bei Buchstaben | ✔ Nur beim ganzen Wort (Abstimmung) |
| 10 | Avatar-Stil | ✔ Vorgegebene Tiere; wer zeichnet, ist noch offen |
| 11 | Abstimmung: Zeitlimit? Was, wenn jemand nicht abstimmt? | offen |
| 12 | Darf man während eines laufenden Spiels beitreten? | offen (vorerst nein) |
| 13 | Unter welcher Domain läuft das Spiel? | offen (eigene Domain, siehe NF-06) |

## 9. Glossar

- **Wortgeber:** wählt das Wort und schaut zu; gewinnt, wenn der Hangman komplett ist.
- **Ratende:** alle anderen; raten gemeinsam.
- **Host:** hat die Lobby erstellt; stellt Regeln ein und startet.
- **Lobby:** Raum, in dem sich Spieler vor dem Start sammeln.
- **Runde:** ein Wort, ein Wortgeber.
- **Durchgang:** alle waren einmal Wortgeber.
- **Polling:** Das Spiel fragt regelmäßig beim Server nach, ob sich etwas geändert hat.
