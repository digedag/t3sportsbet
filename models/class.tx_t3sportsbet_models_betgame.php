<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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

tx_div::load('tx_rnbase_util_DB');
tx_div::load('tx_rnbase_model_base');


/**
 * Model for a betgame.
 */
class tx_t3sportsbet_models_betgame extends tx_rnbase_model_base {
  private static $instances = array();
	function getTableName(){return 'tx_t3sportsbet_betgames';}
	/**
	 * Liefert die Instance mit der übergebenen UID. Die Daten werden gecached, so daß
	 * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
	 *
	 * @param int $uid
	 * @return tx_t3sportsbet_models_betgame
	 */
	static function getInstance($uid) {
		$uid = intval($uid);
		if(!uid) throw new Exception('Invalid uid for betgame');
		if(!is_object(self::$instances[$uid])) {
			self::$instances[$uid] = new tx_t3sportsbet_models_betgame($uid);
		}
		return self::$instances[$uid];
	}
	function getName() {
		return $this->record['name'];
	}
	/**
	 *
	 * @return boolean
	 */
	function isDrawIfExtraTime() {
		return $this->record['draw_if_extratime'] > 0;
	}
	/**
	 *
	 * @return boolean
	 */
	function isDrawIfPenalty() {
		return $this->record['draw_if_penalty '] > 0;
	}
	/**
	 *
	 * @return boolean
	 */
	function isIgnoreGreenTable() {
		return $this->record['ignore_greentable '] > 0;
	}

	/**
	 * Points for exact bet
	 * @return int
	 */
	function getPointsAccurate() {
		return $this->record['points_accurate'];
	}
	/**
	 * Points for tendency bet
	 * @return int
	 */
	function getPointsTendency() {
		return $this->record['points_tendency'];
	}

	/**
	 * Minutes to close bets before match kick off
	 * @return int
	 */
	function getLockMinutes() {
		return intval($this->record['lockminutes']);
	}
	
	/**
	 * Returns the competition for a static bet game
	 *
	 * @return array of tx_cfcleaguefe_models_competition
	 */
	function getCompetitions() {
		$ret = array();
		tx_div::load('tx_cfcleaguefe_models_competition');
		$uids = $this->record['competition'];
		if($uids) {
			$uids = t3lib_div::intExplode(',',$uids);
			foreach($uids As $uid) {
				$ret[] = tx_cfcleaguefe_models_competition::getInstance($uid);
			}
		}
		return $ret;
  }
  /**
   * Returns an array of existing bet sets
   * @return array of tx_t3sportsbet_models_betset
   */
  function getBetSets() {
		$fields['BETSET.BETGAME'][OP_EQ_INT] = $this->uid;
//		$options['debug'] = 1;
		$options['orderby']['BETSET.ROUND'] = 'asc';

		$service = tx_t3sportsbet_util_serviceRegistry::getBetService();
		return $service->searchBetSet($fields, $options);
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_betgame.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_betgame.php']);
}

?>