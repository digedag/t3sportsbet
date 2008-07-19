
Tippspiel

Neues Tippspiel anlegen:
- Name
- Typ -> Flexibel/statisch -> bei Statisch wird ein bestimmter Wettbewerb getippt (alle Spiel)

Bei einem statischen Wettbewerb ist das Tipset identisch mit den Spielrunden. Beim flexiblen Wettbewerb
liegt das Tipset in der Hand des Admins. Er muss Spiele auswählen, die in einem begrenzten Zeitraum liegen.


UseCases FE

Anzeige aller noch zu tippenden Spiele
Anzeige aller getippten Spiele

Regeln für die Anzeige der offenen Tipps
- Anzeige erfolgt rundenweise. Es werden also immer alle Spiele einer offenen Runde angezeigt
- Eingrenzung erfolgt nach Datum. Hier ist nur das Datum in die Zukunft interessant. Spiele aus der 
  Vergangenheit können nicht mehr getippt werden.
- Spiele einer Runde, die schon begonnen hat, werden immer angezeigt (Nachholespiele)

Wenn die Ausgabe rundenweise erfolgt, dann müssen wir immer nach Tipprunden suchen!


Spiel hat drei Zustände:
1. Spiel ist offen (kann getippt werden)
2. Spiel ist geschlossen (kann nicht mehr getippt werden, ist aber noch nicht ausgewertet)
3. Spiel ist gewertet
