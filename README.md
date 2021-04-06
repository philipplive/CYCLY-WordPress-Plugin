# CYCLY WordPress Plugin
![cycly logo](./tpl/cycly-header.jpg)

Mit diesem Plugin können Daten direkt aus [CYCLY](https://cycly.ch/) auf deine WordPress Website eingebunden werden. Die Verwaltung hierfür findent wie gewohnt direkt über das CYCLY-Backend statt und werden dann automatisch auf deiner Website angezeigt. 

## Einbinden in Wordpress
### Tags
Folgende Tags werden aktuell angeboten:

```
[show_vehicles branch=1 manufacturers="scott,santacruz" categories="trekkingbike" types="neufahrzeug,gebrauchtfahrzeug" sort=2]
```
Wichtig: Die Parameter-Werte sind jeweils Komma getrennt, kleingeschrieben, ohne Abstand und Sonderzeichen zu hinterlegen (z.B. "Santa Cruz" wird zu "santacruz". Zweit Hersteller werden entsprechend mit **manufacturers="scott,santacruz"** hinterlegt).
* Optional wird die Auswahl via **branch=1** auf eine Geschäftsstelle beschränkt.
* Optional kann mittels **manufactures="scott"** die Auswahl der möglichen Fahrzeughersteller eingeschränkt werden. 
* Optional kann mittels **categories="citybike,trekkingbike"** die Auswahl der möglichen Fahrzeugkategorien eingeschränkt werden.
* Optional kann mittels **types="neufahrzeug"** die Auswahl der möglichen Fahrzeugtypen eingeschränkt werden.
* Optional kann mittels **onstock="true"** die Auswahl auf aktuell verfügbare Fahrzeuge beschränkt werden.
* Optional kann mittels **sort=4** die Standard-Sortierreihenfolge geändert werden (1 = Preis absteigend, 2 = Preis aufsteigend (standard), 3 = Baujahr absteigend, 4 ) Baujahr aufsteigend.
* Optional kann mittels **sortable="false"** die Sortierfunktion ausgeblendet werden.
* Optional kann mittels **limit=3** die maximale Anzahl Fahrzeuge welche direkt angezeigt werden (mehr via button "Mehr anzeigen").

```
[show_employees branch="1"]
```
* Anzeige der Mitarbeiter der entsprechenden Geschäftsstelle

### Widgets
Folgende Widgets werden aktuell angeboten:
* Öffnungszeiten inkl. Feiertage

## Installation

Um das Plugin zu installieren, erstellen Sie bitte folgenden Ordner "/wp-content/plugins/**cycly-connector**" und extrahieren den Zip-Download von GitHub direkt hinein. Das Plugin kann nun im WordPress Backend aktiviert werden. Im Hauptmenu erscheint nun ein entsprechender Menupunkt "CYCLY-Connector".

Um nun die Schnittstelle nutzen zu können, muss im Plugin die Schnittstelle konfiguriert werden. Die korrekten REST-Api Zugangsdaten können im CYCLY unter dem Menupunkt ***Einstellungen > REST-Api*** generiert werden.
