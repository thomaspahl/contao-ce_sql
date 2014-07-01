ce_sql: SQL Abfrage als Content-Element
=======================================

Mit dem Contao-Inhaltselement **SQL** kann in einfacher Weise die Ausgabe einer beliebigen Datenbank-Abfrage eingebunden werden.

In gewisser Weise ist es eine vereinfachte Form von **mod-listing** (und auch aus einer erweiterten Version davon abgeleitet) und kann da eingesetzt werden wo **mod_listing** zu lästig/aufwendig ist (Modul plus Content-Element) oder nicht möglich ist (wegen der Begrenzung auf existierende Tabellen und in der Formulierung).
 
Der Funktionsmfang ist bewusst einfach gehalten. Nicht unterstützt sind interaktive Möglichkeiten wie: Sortieren, Blättern (Pagination), Suche - dafür ist **mod_listing** die richtige Wahl.

SQL Abfrage
-----------
Die Datenbank-Abfrage kann frei formuliert werden, sie ist an keine Tabellen-Definition (DCA) gebunden. Es werden immer alle Ergebniszeilen ausgegeben - es ist Sache der Abfrage, die Ergebnismenge vernünftig zu begrenzen. Ausreichende SQL-Kenntnisse werden vorausgesetzt (man kann sich herantasten), die Freigabe für Redakteure sollte gut bedacht werden. **Inserttags** in der Abfrage sind unterstützt und werden jeweils unmittelbar vor Ausführung der Abfrage ersetzt (um Inserttags unersetzt in die Ausgabe zu übertragen, muss man sie mittels CONCAT() zerlegen).

Die Ausgabe erfolgt per default als Tabelle, über die anderen Templates sind ungeordnete und geordnete Liste möglich. Der Tabellenkopf mit den Spalten-Überschriften ist wahlweise unterdrückbar (bei den Listen gibt es kine Überschriften).

Spalten-Überschriften
---------------------
Standard-Spaltenüberschrift ist der Spaltenname, was oft wenig hübsch aussieht. Eine beliebige Spalten-Überschrift kann durch das Column-Alias (`column AS "Spaltenüberschrift"`) leicht erzeugt werden.

Über `$GLOBALS['TL_LANG']['ce_sql'][columns-alias] = 'Text...'` kann die Spaltenüberschrift ebenfalls (optional) ersetzt werden (ohne eventuellen Formatierungscode, s.u.).

Werte-Formatierung
------------------
Alle Werte, die in weitem Sinne wie Zahlen aussehen (nur Ziffern und Punkt, Komma, Leerzeichen enthalten), erhalten `class="numeric"`, so dass sie z.B. per CSS rechtsbündig formatiert werden können. Serialisierte Werte werden de-serialisiert komma-getrennt ausgegeben, werden allerdings nicht hinsichtlich einer Wertetabelle (z.B. `member_group`) aufgelöst (diese ist ohne DCA ja auch nicht bekannt).

Darüber hinaus ist eine explizite Formatangabe durch Anhängen eines Formatierungscodes wie etwa `:date` an das Column-Alias möglich, z.B.
```
	SELECT change_date AS "Letzte Änderung:datetime", comment AS "Kommentar" FROM table
```

Folgende Formatierungscodes werden unterstützt:
* `:date` - als Datum
* `:time` - als Uhrzeit
* `:datetime` - als Datum mit Uhrzeit
* `:email` - als E-Mail, es wird automatisch ein E-Mail Link erzeugt
* `:url` - als Weblink, es wird automatisch ein Link-Anker erzeugt
* `:uuid` - als UUID, lesbar anzeigen
* `:none` - unterdrückt eine automatische Spaltenformatierung (s. nächstens Absatz)

Automatische Formate: Einige Spaltennamen werden automatisch formatiert, wenn keine explizite Angabe vorliegt:
* tstamp, deadline, dateAdded, lastLogin, currentLogin als `:datetime`
* date als `:date`
* time als `:time`
* email als `:email`
* website als `:url`
* uuid als `:uuid`

Um dies zu verhindern, kann man explizit den Formatcode `:none` angeben.

Die Werte-Formatierung funktioniert auch, wenn die Spalten-Überschriften unterdrückt werden.

Bekannte Bugs und Einschränkungen
---------------------------------

* Die automatische De-Serialisierung funktioniert nur für einfache Arrays.

Erweiterungsideen
-----------------
Dass man vielfach logische Tabellenverknüpfungen nicht per SQL abfragen kann, weil die zugehörigen Foreign Keys in serialisierten Feldern abgelegt sind (`member.groups` u.v.a.m.) nervt einen alten Datenbänker. Bei einem sauberen Datenbank-Design wären hier ordentliche Verknüpfungstabellen (join tables) im Einsatz, samt konsistenter Foreign-Key-Deklaration. Contao ist davon leider weit entfernt. Ich überlege, ob man im Einzelfall zumindest die Werte-Auflösung doch automatisch hin bekommt.

DBAFS UUID Dekodierung: Für Einzelwerte lässt sich das per JOIN in der Abfrage auflösen. Für serialisierte Mehrfach-Felder gilt grundsätzlich der vorhergehende Absatz, ist hier im Spezialfall evtl. einfacher umzusetzen.