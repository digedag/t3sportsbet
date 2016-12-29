<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2016 Rene Nitzsche (rene@system25.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_cfcleaguefe_util_MatchMarker');
tx_rnbase::load('tx_rnbase_util_Templates');


/**
 * Diese Klasse ist für die Erstellung von Markerarrays der Tipprunden verantwortlich
 */
class tx_t3sportsbet_util_MatchMarker extends tx_rnbase_util_BaseMarker {
	static $betMarker = null;
	function __construct($options = array()) {
		$this->options = $options;
		$this->matchMarker = tx_rnbase::makeInstance('tx_cfcleaguefe_util_MatchMarker');
//		$this->betMarker = tx_rnbase::makeInstance('tx_t3sportsbet_util_BetMarker');
	}

	/**
	 * @return tx_t3sportsbet_util_BetMarker
	 */
	private static function getBetMarker() {
		if(!self::$betMarker)
			self::$betMarker = tx_rnbase::makeInstance('tx_t3sportsbet_util_BetMarker');
		return self::$betMarker;
	}
	/**
	 * @param string $template das HTML-Template
	 * @param tx_cfcleaguefe_models_match $match das Spiel
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $matchConfId Pfad der TS-Config des Spiels, z.B. 'listView.match.'
	 * @param string $matchMarker Name des Markers für ein Spiel, z.B. MATCH
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, &$match, &$formatter, $confId, $marker = 'MATCH') {
		$betset = $this->options['betset'];
		$feuser = $this->options['feuser'];

		// Set some registers for TS
		$GLOBALS['TSFE']->register['T3SPORTS_MATCHUID'] = $match->getUid();
		$GLOBALS['TSFE']->register['T3SPORTS_MATCHSTATE'] = $match->getProperty('status');
		$GLOBALS['TSFE']->register['T3USERS_USERUID'] = is_object($feuser) ? $feuser->getUid() : 0;
		$GLOBALS['TSFE']->register['T3SPORTSBET_BETSETUID'] = $betset->getUid();
		$GLOBALS['TSFE']->register['T3SPORTSBET_BETSETSTATUS'] = $betset->getProperty('status');
		$GLOBALS['TSFE']->register['T3SPORTSBET_BETSTATUS'] = $betset->getMatchState($match);

		// Die Tipptendenz mit einblenden
		if((self::containsMarker($template, $marker .'_TREND')))
			$this->addBetTrend($betset, $match);
		if((self::containsMarker($template, $marker .'_STATS')))
			$this->addBetStats($betset, $match);

		// Für T3sports muss der Qualifier geändert werden, damit die Verlinkung klappt
		$formatter->getConfigurations()->setQualifier('cfc_league_fe');
		$template = $this->matchMarker->parseTemplate($template, $match, $formatter, $confId, $marker);
		$formatter->getConfigurations()->setQualifier( $formatter->getConfigurations()->get('qualifier') );

		$this->pushTT('setForm');
		$bet = $betset->getBet($match, $feuser);
		$template = $this->setForm($template, $betset, $bet, $feuser, $formatter);
		$this->pullTT();
		$template = self::getBetMarker()->parseTemplate($template, $bet, $formatter, $confId.'bet.', $marker.'_BET');
		return $template;
	}
	/**
	 * Tiptrend für das Spiel einsetzen
	 *
	 * @param tx_t3sportsbet_models_betset $betset
	 * @param tx_cfcleaguefe_models_match $match
	 */
	public function addBetTrend($betset, $match) {
		$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$trend = $srv->getBetTrend($betset, $match);
		$match->setProperty( array_merge($match->getProperty(), $trend) );
	}
	/**
	 * Tipstatistik für das Spiel einsetzen. Diese Daten sind erst nach der Auswertung des Spiels möglich.
	 *
	 * @param tx_t3sportsbet_models_betset $betset
	 * @param tx_cfcleaguefe_models_match $match
	 */
	public function addBetStats($betset, $match) {
		$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$trend = $srv->getBetStats($betset, $match);

		$match->setProperty( array_merge($match->getProperty(), $trend) );
	}
	/**
	 * Render form
	 *
	 * @param string $template
	 * @param tx_t3sportsbet_models_betset $betset
	 * @param tx_t3sportsbet_models_bet $bet
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @return string
	 */
	function setForm($template, $betset, $bet, $feuser, $formatter) {

		$subpartArray['###BETSTATUS_OPEN###'] = '';
		$subpartArray['###BETSTATUS_CLOSED###'] = '';
		$subpartArray['###BETSTATUS_FINISHED###'] = '';
		// Ohne FE-User setzen wir die Anzeige immer auf CLOSED
		// Gleiches gilt, wenn der aktuelle User != FE-User ist
		$state = 'CLOSED';
		if($feuser) {
			$state = $betset->getMatchState($bet->getMatch());
			if($state == 'OPEN') {
				// Prüfen, ob der aktuelle User seinen eigenen Tip bearbeiten will
				tx_rnbase::load('tx_t3users_models_feuser');
				$currUser = tx_t3users_models_feuser::getCurrent();
				if(!($currUser && $currUser->getUid() == $feuser->getUid()))
					$state = 'CLOSED';
			}
		}
		// Hier benötigen wir eigentlich einen Observer, dem wir sagen, daß ein Spiel offen ist. Wir setzen das jetzt
		// einfach mal in die Config...
		if($state == 'OPEN') $formatter->getConfigurations()->getViewData()->offsetSet('MATCH_STATE', 'OPEN');
		$subTemplate = tx_rnbase_util_Templates::getSubpart($template,'###BETSTATUS_'.$state.'###');
		$subpartArray['###BETSTATUS_'.$state.'###'] = $subTemplate;
		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray); //, $wrappedSubpartArray);

		return $out;
	}
}

