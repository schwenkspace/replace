rules# Replacer

Mittels des 'Replacers' kannst Du per Konsole textbasierte DBen in einem Verzeichnis auf Begriffe durchsuchen und ersetzen.

### Prerequisites

- replacer.php
- *.json 

### How To
  
 Funktionsweise des 'Replacers': 
 
 - Aufruf des Scripts: 'php Pfad/zum/Script/replacer.php' 

 Das Script benötigt mindestens zwei Parameter:
 
 - Pfad zur 'Regeldatei': '--rules /Pfad/zur/Regeldatei.json'
 - Pfad zum zu bearbeitenden Verzeichnis: '--searchdir /Pfadangabe/zum/zu/durchsuchenden/Verzeichnis'
 
 Beispiel: 
 'php  /home/mein_name/htdocs/replacer/replacer.php --rules /home/mein_name/htdocs/regeln.json --searchdir /home/mein_name/htdocs/serverumzug/' 
 
 Die Regeldatei muss im json-Format vorliegen, siehe 'example.rules.json' 
 
 
 Des Weiteren sind folgende optionale Parameter verfügbar : 

 – mittels '--no-recursive' wird nur das mit '--searchdir' angegebene Verzeichnis ausgelesen und bearbeitet, Unterverzeichnisse werden ignoriert.
 
 – mittels '--dry' wird nur ein Trockenlauf durchgeführt und keine Änderung vorgenommen.
 
 – mittels '--verbose' kann ausgegeben werden, welche Dateien untersucht und welche bearbeitet wurden (oder mittels --dry bearbeitet werden WÜRDEN). 
 optionale Parameter:
  '--verbose 0' -> keine Ausgabe 
  '--verbose 1' -> Ausgabe, welche Dateien geändert wurden [Standardoption, wenn User keine Angabe gemacht hat] 
  '--verbose 2' -> Ausgabe, welche Dateien untersucht wurden 
 
 Die Hilfe wird mittels 'php pfad/angabe/replacer.php --help' aufgerufen 





