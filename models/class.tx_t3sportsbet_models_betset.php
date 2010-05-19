<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Rene Nitzsche (rene@system25.de)
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_model_base');


/**
 * Model for a betset.
 */
class tx_t3sportsbet_models_betset extends tx_rnbase_model_base {
  private static $instances = array();
	function getTableName(){return 'tx_t3sportsbet_betsets';}

	/**
	 * Returns the betgame
	 *
	 * @return tx_t3sportsbet_models_betgame
	 */
	function getBetgame() {
		return tx_t3sportsbet_models_betgame::getInstance($this->record['betgame']);
	}
	/**
	 * Returns the bet state of a match. This can be OPEN, CLOSED or FINISHED
	 * OPEN -> new bets are possible
	 * CLOSED -> bets are not possible, but not analyzed
	 * FINISHED -> bets are analyzed
	 *
	 * @param tx_cfcleaguefe_models_match $match
	 */
	function getMatchState($match) {
		// Das Spiel ist finished, wenn es ausgewertet und in die Tipstatistik des Users
		// aufgenommen wurde -> Es hängt am User
		// TODO: Wir sollten das über den Tip des Users ermitteln
		if($this->isFinished() || $match->isFinished())
			return 'FINISHED';
		$state = 'OPEN';
		$now = tx_t3sportsbet_util_library::getNow();
		$lock = $this->getBetgame()->getLockMinutes() * 60;
		
		$matchDate = $match->record['date'];
		if($matchDate <= ($now+$lock) || $match->isRunning()) {
			$state = 'CLOSED';
		}
		return $state;
	}
	/**
	 * Returns all matches of this bet set
	 *
	 * @return array of tx_cfcleaguefe_models_match
	 */
	function getMatches() {
		$service = tx_cfcleaguefe_util_ServiceRegistry::getMatchService();
		$fields['BETSETMM.UID_LOCAL'][OP_EQ_INT] = $this->uid;
		$options['orderby']['BETSETMM.SORTING'] = 'asc';
		return $service->search($fields, $options);
	}
	/**
	 * Returns the bet for a match
	 *
	 * @param tx_cfcleaguefe_models_match $match
	 * @param tx_t3users_models_feuser $feuser
	 */
	function getBet($match, $feuser) {
		$service = tx_t3sportsbet_util_serviceRegistry::getBetService();
		return $service->getBet($this, $match, $feuser);
	}

	/**
	 * Returns the number of bets for a match in this betset
	 *
	 * @param tx_cfcleaguefe_models_match $match
	 * @return int
	 */
	function getBetCount($match) {
		$service = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$fields['BET.BETSET'][OP_EQ_INT] = $this->uid;
		$fields['BET.T3MATCH'][OP_EQ_INT] = $match->uid;
		$options['count'] = 1;
//		$options['debug'] = 1;
		return $service->searchBet($fields, $options);;
	}
	function getName() {
		return $this->record['round_name'];
	}
	/**
	 * Whether or not bets can be made to this betset
	 *
	 * @return boolean
	 */
	function isFinished() {
		return $this->record['status'] == 2;
	}
	function getStatus() {
		return $this->record['status'];
	}
	/**
	 * Liefert die Instance mit der übergebenen UID. Die Daten werden gecached, so daß
	 * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
	 *
	 * @param int $uid
	 * @return tx_t3sportsbet_models_betset
	 */
	static function getInstance($uid) {
		$uid = intval($uid);
		if(!uid) throw new Exception('Invalid uid for betset');
		if(!is_object(self::$instances[$uid])) {
			self::$instances[$uid] = new tx_t3sportsbet_models_betset($uid);
		}
		return self::$instances[$uid];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_betset.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_betset.php']);
}

?>