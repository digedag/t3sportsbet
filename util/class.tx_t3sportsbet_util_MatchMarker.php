<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');

tx_div::load('tx_rnbase_util_BaseMarker');
tx_div::load('tx_cfcleaguefe_util_MatchMarker');

/**
 * Diese Klasse ist für die Erstellung von Markerarrays der Tipprunden verantwortlich
 */
class tx_t3sportsbet_util_MatchMarker extends tx_rnbase_util_BaseMarker {
	function tx_t3sportsbet_util_MatchMarker($options = array()) {
		$this->options = $options;
		$markerClass = tx_div::makeInstanceClassName('tx_cfcleaguefe_util_MatchMarker');
    $this->matchMarker = new $markerClass;
		$markerClass = tx_div::makeInstanceClassName('tx_t3sportsbet_util_BetMarker');
    $this->betMarker = new $markerClass;
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
		$GLOBALS['TSFE']->register['T3SPORTS_MATCHUID'] = $match->uid;
		$GLOBALS['TSFE']->register['T3SPORTS_MATCHSTATE'] = $match->record['status'];
		$GLOBALS['TSFE']->register['T3USERS_USERUID'] = $feuser->uid;
		$GLOBALS['TSFE']->register['T3SPORTSBET_BETSETUID'] = $betset->uid;
		$GLOBALS['TSFE']->register['T3SPORTSBET_BETSETSTATUS'] = $betset->record['status'];
		$GLOBALS['TSFE']->register['T3SPORTSBET_BETSTATUS'] = $betset->getMatchState($match);
		
		// Die Tipptendenz mit einblenden
  	if((self::containsMarker($template, $marker .'_TREND')))
			$this->addBetTrend($betset, $match);
		
		// Für T3sports muss der Qualifier geändert werden, damit die Verlinkung klappt
		$formatter->configurations->_qualifier = 'cfc_league_fe';
		$template = $this->matchMarker->parseTemplate($template, $match, $formatter, $confId, $marker);
		$formatter->configurations->_qualifier = $formatter->configurations->get('qualifier');

		$this->pushTT('setForm');
		$bet = $betset->getBet($match, $feuser);
		$template = $this->setForm($template, $betset, $bet, $feuser, $formatter);
		$this->pullTT();
		$template = $this->betMarker->parseTemplate($template, $bet, $formatter, $confId.'bet.', $marker.'_BET');
		return $template;
  }
  /**
   * Tiptrend für das Spiel einsetzen
   *
   * @param tx_t3sportsbet_models_betset $betset
   * @param tx_cfcleaguefe_models_match $match
   */
  public function addBetTrend(&$betset, &$match) {
  	$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
  	$trend = $srv->getBetTrend($betset, $match);
  	$match->record = array_merge($match->record, $trend);
//  	t3lib_div::debug($trend, 'tx_t3sportsbet_util_MatchMarker'); // TODO: remove me
  }
  /**
   * Render form
   *
   * @param string $template
   * @param tx_t3sportsbet_models_betset $betset
   * @param tx_t3sportsbet_models_bet $bet
   * @param tx_t3users_models_feuser $feuser
   * @param tx_rnbase_util_FormUtil $formatter
   * @return string
   */
	function setForm($template, $betset, $bet, $feuser, &$formatter) {
		
		$subpartArray['###BETSTATUS_OPEN###'] = '';
		$subpartArray['###BETSTATUS_CLOSED###'] = '';
		$subpartArray['###BETSTATUS_FINISHED###'] = '';
		// Ohne FE-User setzen wir die Anzeige immer auf CLOSED
		$state = 'CLOSED';
		if($feuser)
			$state = $betset->getMatchState($bet->getMatch());
		// Hier benötigen wir eigentlich einen Observer, dem wir sagen, daß ein Spiel offen ist. Wir setzen das jetzt 
		// einfach mal in die Config...
		if($state == 'OPEN') $formatter->configurations->getViewData()->offsetSet('MATCH_STATE', 'OPEN');

		$subTemplate = $formatter->cObj->getSubpart($template,'###BETSTATUS_'.$state.'###');
		$subpartArray['###BETSTATUS_'.$state.'###'] = $subTemplate;

    $out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray); //, $wrappedSubpartArray);

//		t3lib_div::debug($state, 'tx_t3sportsbet_util_MatchMarker'); // TODO: Remove me!
		return $out;
	}
  
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_MatchMarker.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_MatchMarker.php']);
}
?>