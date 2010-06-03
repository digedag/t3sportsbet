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
tx_rnbase::load('tx_rnbase_util_Dates');



/**
 * Model for a team question.
 */
class tx_t3sportsbet_models_teamquestion extends tx_rnbase_model_base {
	function getTableName(){return 'tx_t3sportsbet_teamquestions';}

	/**
	 * Returns the betset
	 *
	 * @return tx_t3sportsbet_models_betset
	 */
	public function getBetSet() {
		return tx_t3sportsbet_models_betset::getInstance($this->getBetSetUid());
	}
	/**
	 * Returns the uid of betset
	 *
	 * @return int
	 */
	public function getBetSetUid() {
		return $this->record['betset'];
	}

	/**
	 * Whether or not this team question is still open
	 * @return boolean
	 */
	public function isOpen() {
		return $this->getOpenUntilTstamp() > time();
	}
	/**
	 * Return the question string
	 * @return string
	 */
	public function getQuestion() {
		return $this->record['question'];
	}
	public function getOpenUntil() {
		return $this->record['openuntil'];
	}
	public function getPoints() {
		return $this->record['points'];
	}
	/**
	 * Returns the uid of winning team
	 * @return int
	 */
	public function getTeamUid() {
		return $this->record['team'];
	}
	public function getOpenUntilTstamp() {
		return tx_rnbase_util_Dates::datetime_mysql2tstamp($this->getOpenUntil());
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_teamquestion.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/models/class.tx_t3sportsbet_models_teamquestion.php']);
}

?>