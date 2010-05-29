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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
require_once(PATH_t3lib.'class.t3lib_svbase.php');

tx_rnbase::load('tx_t3sportsbet_util_library');


/**
 * 
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_services_teambet extends t3lib_svbase  {

	public function searchTeamQuestion($fields, $options) {
		tx_rnbase::load('tx_rnbase_util_SearchBase');
		$searcher = tx_rnbase_util_SearchBase::getInstance('tx_t3sportsbet_search_TeamQuestion');
		return $searcher->search($fields, $options);
	}

	/**
	 * TODO!
	 * @param unknown_type $teamQuestion
	 * @param unknown_type $feuser
	 */
	public function getTeamQuestionStatus($teamQuestion, $feuser) {
		$state = 'CLOSED';
		if($feuser) {
			$state = $betset->getMatchState($bet->getMatch());
			if($state == 'OPEN') {
				// Prüfen, ob der aktuelle User seinen eigenen Tip bearbeiten will
				tx_rnbase::load('tx_t3users_models_feuser');
				$currUser = tx_t3users_models_feuser::getCurrent();
				if(!($currUser && $currUser->uid == $feuser->uid))
					$state = 'CLOSED';
			}
		}
	}
	/**
	 * Load all teams for a given betgame
	 * @param tx_t3sportsbet_models_betgame $betgame
	 */
	public function getTeams4Betgame($betgame) {
		//$betgame->getCompetitions();
		// Search for teams
		$fields = array();
		$fields['COMPETITION.UID'][OP_IN_INT] = $betgame->record['competition'];
		$options = array();
		$options['distinct'] = 1;
		$options['orderby']['TEAM.NAME'] = 'asc';
		$srv = tx_cfcleague_util_ServiceRegistry::getTeamService();
		return $srv->searchTeams($fields, $options);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/services/class.tx_t3sportsbet_services_teambet.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/services/class.tx_t3sportsbet_services_teambet.php']);
}

?>