
# T3sportsbet

----

![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-8.7%20%7C%209.5%20%7C%2010.4%20%7C%2011.5%20%7C%2012.4-orange?maxAge=3600&style=flat-square&logo=typo3)
<a href="https://github.com/digedag/t3sportsbet"><img src="ext_icon.svg" width="20"></a>
[![Latest Stable Version](https://img.shields.io/packagist/v/digedag/t3sportsbet.svg?maxAge=3600)](https://packagist.org/packages/digedag/t3sportsbet)
[![Total Downloads](https://img.shields.io/packagist/dt/digedag/t3sportsbet.svg?maxAge=3600)](https://packagist.org/packages/digedag/t3sportsbet)
[![Code Style](https://github.com/digedag/t3sportsbet/actions/workflows/php.yaml/badge.svg)](https://github.com/digedag/t3sportsbet/actions/workflows/php.yaml)
<a href="https://twitter.com/intent/follow?screen_name=T3sports1">
  <img src="https://img.shields.io/twitter/follow/T3sports1.svg?label=Follow%20@T3sports1" alt="Follow @T3sports1" />
</a>

## Neues Tippspiel anlegen:

- Name
- Typ -> Flexibel/statisch -> bei Statisch wird ein bestimmter Wettbewerb getippt (alle Spiele)

Bei einem statischen Wettbewerb ist das Tipset identisch mit den Spielrunden. Beim flexiblen Wettbewerb
liegt das Tipset in der Hand des Admins. Er muss Spiele auswählen, die in einem begrenzten Zeitraum liegen.


## UseCases FE

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
1. Spiel ist geschlossen (kann nicht mehr getippt werden, ist aber noch nicht ausgewertet)
1. Spiel ist gewertet
