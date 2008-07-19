<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (rene@system25.de)
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

// Die Datenbank-Klasse
require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');

tx_div::load('tx_rnbase_model_base');


/**
 * Model for a bet.
 */
class tx_t3sportsbet_models_bet extends tx_rnbase_model_base {
	function getTableName(){return 'tx_t3sportsbet_bets';}
	
	/**
	 * Returns the match
	 *
	 * @return tx_cfcleaguefe_models_match
	 */
	function getMatch() {
		tx_div::load('tx_cfcleaguefe_models_match');
		return tx_cfcleaguefe_models_match::getInstance($this->record['t3match']);
	}

	/**
	 * Returns the betgame
	 *
	 * @return tx_t3sportsbet_models_betset
	 */
	function getBetSet() {
		return tx_t3sportsbet_models_betset::getInstance($this->record['betset']);
	}
	/**
	 * Goals home
	 * @return int
	 */
	function getGoalsHome() {
		return intval($this->record['goals_home']);
	}
	/**
	 * Goals guest
	 * @return int
	 */
	function getGoalsGuest() {
		return intval($this->record['goals_guest']);
	}
	function getToto() {
		$goalsHome = $this->getGoalsHome();
		$goalsGuest = $this->getGoalsGuest();
		$goalsDiff = $goalsHome - $goalsGuest;
		if($goalsDiff == 0)
			return 0;
		return ($goalsDiff < 0) ? 2 : 1;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_bet.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_bet.php']);
}

?>