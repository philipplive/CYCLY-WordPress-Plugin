# CYCLY WordPress Plugin
![cycly logo](./tpl/cycly-header.jpg)

Mit diesem Plugin können Daten direkt aus [CYCLY](https://cycly.ch/) auf deine WordPress Website eingebunden werden. Die Verwaltung hierfür findent wie gewohnt direkt über das CYCLY-Backend statt und werden dann automatisch auf deiner Website angezeigt. 

## Einbinden in Wordpress
### Tags
Folgende Tags werden aktuell angeboten:

```
[show_vehicles branch="1" manufacturers="scott,santacruz" categories="trekkingbike" types="neufahrzeug,gebrauchtfahrzeug" sort="2"]
```
* Anzeige der Fahrzeuge.
* Geschäftsstelle **branch**.
* Optional kann mittels **manufactures** die Auswahl der möglichen Fahrzeughersteller eingeschränkt werden.
* Optional kann mittels **categories** die Auswahl der möglichen Fahrzeugkategorien eingeschränkt werden.
* Optional kann mittels **types** die Auswahl der möglichen Fahrzeugtypen eingeschränkt werden.
* Optional kann mittels **onstock="true"** die Auswahl auf aktuell verfügbare Fahrzeuge beschränkt werden.
* Optional kann mittels **sort** die Standard-Sortierreihenfolge geändert werden (1 = Preis absteigend, 2 = Preis aufsteigend (standard), 3 = Baujahr absteigend, 4 ) Baujahr aufsteigend
* Optional kann mittels **sortable="false"** die Sortierfunktion ausgeblendet werden.
* Optional kann mittels **limit="3""** die maximale Anzahl Fahrzeuge welche direkt angezeigt werden (mehr via button "Mehr anzeigen").

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
