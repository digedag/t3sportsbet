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


	/**
	 * Returns the teambet for a user 
	 * If no bet is found this method return a dummy instance of tx_t3sportsbet_models_teambet
	 * with uid=0.
	 *
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @param tx_t3users_models_feuser $feuser
	 * @return tx_t3sportsbet_models_teambet 
	 */
	public function getTeamBet($teamQuestion, $feuser) {
		$ret = array();
		if($feuser) {
			// Ohne FE-User kann die DB-Abfragen gespart werden
			$fields['TEAMBET.QUESTION'][OP_EQ_INT] = $teamQuestion->uid;
			$fields['TEAMBET.FEUSER'][OP_EQ_INT] = $feuser->uid;
//			$options['debug'] = 1;
			$ret = $this->searchTeamBet($fields, $options);
		}
		
		$bet = count($ret) ? $ret[0] : null;
		if(!$bet) {
			// No bet in database found. Create dummy instance
			$bet = tx_rnbase::makeInstance('tx_t3sportsbet_models_teambet', array('uid' => 0,
						'question' => $teamQuestion->uid,
						'fe_user' => $feuser->uid));
		}
		return $bet;
	}
	/**
	 * Is a teambet possible for a user.
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @param tx_t3users_models_feuser $feuser 
	 */
	public function getTeamQuestionStatus($teamQuestion, $feuser) {
		$state = 'CLOSED';
		if($feuser) {
			$state = $teamQuestion->isOpen() ? 'OPEN' : $state;
			if($state == 'OPEN') {
				// Prüfen, ob der aktuelle User seinen eigenen Tip bearbeiten will
				tx_rnbase::load('tx_t3users_models_feuser');
				$currUser = tx_t3users_models_feuser::getCurrent();
				if(!($currUser && $currUser->uid == $feuser->uid))
					$state = 'CLOSED';
			}
		}
		return $state;
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

	/**
	 * Save or update a teambet from fe request.
	 *
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @param tx_t3users_models_feuser $feuser
	 * @param int $betUid
	 * @param int $teamUid
	 * @return int 0/1 whether the bet was saved or not
	 */
	public function saveOrUpdateBet($teamQuestion, $feuser, $betUid, $teamUid) {
		$betset = $teamQuestion->getBetSet();
		if(!$teamQuestion->isOpen()) return 0;
		if($betset->isFinished()) return 0;

		$teamUid = intval($teamUid);
		if(!$teamUid) return 0; // No values given
		// Der Tip muss vom selben User stammen
		$values = array();
		$values['tstamp'] = time();
		$values['team'] = $teamUid;
		$values['possiblepoints'] = $teamQuestion->getPoints();
		$betUid = intval($betUid);
		if($betUid) {
			// Update bet
			$bet = tx_rnbase::makeInstance('tx_t3sportsbet_models_teambet', $betUid);
			if($bet->record['feuser'] != $feuser->uid) return 0;
			if($bet->record['team'] == $values['team']) return 0;
			$where = 'uid=' . $betUid;
			tx_rnbase_util_DB::doUpdate('tx_t3sportsbet_teambets', $where, $values, 0);
		}
		else {
			// Create new teambet instance
			// Ein User darf pro Frage nur einen Tip abgeben
			$bet = $this->getTeamBet($teamQuestion, $feuser);
			if($bet->isPersisted()) return 0; // There is already a bet for this match!
			
			$values['pid'] = $teamQuestion->record['pid'];
			$values['crdate'] = $values['tstamp'];
			$values['feuser'] = $feuser->uid;
			$values['question'] = $teamQuestion->uid;
			tx_rnbase_util_DB::doInsert('tx_t3sportsbet_teambets', $values, 0);
		}
		return 1;
	}
	public function searchTeamQuestion($fields, $options) {
		tx_rnbase::load('tx_rnbase_util_SearchBase');
		$searcher = tx_rnbase_util_SearchBase::getInstance('tx_t3sportsbet_search_TeamQuestion');
		return $searcher->search($fields, $options);
	}
	public function searchTeamBet($fields, $options) {
		tx_rnbase::load('tx_rnbase_util_SearchBase');
		$searcher = tx_rnbase_util_SearchBase::getInstance('tx_t3sportsbet_search_TeamBet');
		return $searcher->search($fields, $options);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/services/class.tx_t3sportsbet_services_teambet.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/services/class.tx_t3sportsbet_services_teambet.php']);
}

?>