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
 * Model for a bet.
 */
class tx_t3sportsbet_models_teambet extends tx_rnbase_model_base {
	function getTableName(){return 'tx_t3sportsbet_teambets';}
	
	/**
	 * Returns the betgame
	 *
	 * @return tx_t3sportsbet_models_teamquestion
	 */
	public function getTeamQuestion() {
		return tx_rnbase::makeInstance('tx_t3sportsbet_models_teamquestion', $this->record['question']);
	}
	public function getTeamQuestionUid() {
		return $this->record['question'];
	}
	/**
	 * Possible points
	 * @return int
	 */
	public function getPossiblePoints() {
		return intval($this->record['possiblepoints']);
	}
	/**
	 * Team
	 * @return int
	 */
	public function getTeamUid() {
		return intval($this->record['team']);
	}
	/**
	 * Team
	 * @return int
	 */
	public function getTeam() {
		return tx_rnbase::makeInstance('tx_cfcleague_models_Team', $this->record['team']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_teambet.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_teambet.php']);
}

?>