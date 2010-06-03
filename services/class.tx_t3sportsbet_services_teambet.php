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
	 * Analyze teambets of a betgame
	 *
	 * @param tx_t3sportsbet_models_betgame $betGame
	 * @return int number of finished bets
	 */
	public function analyzeBets($betGame) {
		// Ablauf
		// Tips ohne Auswertung, deren Spiele beendet sind
		$fields['BETSET.BETGAME'][OP_EQ_INT] = $betGame->uid;
		$fields['TEAMBET.FINISHED'][OP_EQ_INT] = 0;
		$fields['TEAMQUESTION.TEAM'][OP_GT_INT] = 0; // Team ist gesetzt
		
//		$options['debug'] = 1;
		// This could be memory consuming...
		$bets = $this->searchTeamBet($fields, $options);
		// Für jeden Tip die TeamQuestion holen. Danach die Teams vergleichen
		$ret = 0;
		for($i=0, $cnt = count($bets); $i < $cnt; $i++) {
			$bet = $bets[$i];
			$question = $this->loadTeamQuestion($bet->getTeamQuestionUid());
			$values['finished'] = 1;
			$values['points'] = $question->isWinningBet($bet) ? $bet->record['possiblepoints'] : 0;
			$where = 'uid=' . $bet->uid;
			tx_rnbase_util_DB::doUpdate('tx_t3sportsbet_teambets', $where, $values, 0);
			$ret++;
		}
		return $ret;
	}
	/**
	 * Reset all bets for team questions
	 * @param mixed $teamQuestionUids commaseparated uids of team questions
	 * @return int number of bets
	 */
	public function resetTeamBets($teamQuestionUids) {
		$teamQuestionUids = implode(',',t3lib_div::intExplode(',', $teamQuestionUids));
		if(!$teamQuestionUids) return;
		$values['finished'] = 0;
		$values['points'] = 0;
		$where = 'question IN (' . $teamQuestionUids . ')';
		return tx_rnbase_util_DB::doUpdate('tx_t3sportsbet_teambets', $where, $values, 0);
	}
	
	/**
	 * Load a teamquestion from database. This access is cached.
	 * @param int $uid
	 * @return tx_t3sportsbet_models_teamquestion
	 */
	public function loadTeamQuestion($uid) {
		tx_rnbase::load('tx_rnbase_cache_Manager');
		$cache = tx_rnbase_cache_Manager::getCache('t3sports');
		$question = $cache->get('t3sbet_tq_'.$uid);
		if(!$question) {
			$question = tx_rnbase::makeInstance('tx_t3sportsbet_models_teamquestion', $uid);
			$cache->set('t3sbet_tq_'.$uid, $question);
		}
		return $question;
	}
	/**
	 * Returns the number of bets for a teamquestion
	 *
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @return int
	 */
	public function getBetCount($teamQuestion) {
		$fields['TEAMBET.QUESTION'][OP_EQ_INT] = $teamQuestion->getUid();
		$options['count'] = 1;
		return $this->searchTeamBet($fields, $options);;
	}

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
	 * Load all possible teams for a given teamquestion
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 */
	public function getTeams4TeamQuestion($teamQuestion) {
		$srv = tx_cfcleague_util_ServiceRegistry::getTeamService();
		$fields = array();
		$fields['TEAMQUESTIONMM.UID_LOCAL'][OP_EQ_INT] = $teamQuestion->getUid();
		$fields['TEAMQUESTIONMM.TABLENAMES'][OP_EQ] = 'tx_cfcleague_teams';

		$options = array();
		$options['orderby']['TEAMQUESTIONMM.SORTING'] = 'asc';
		return $srv->searchTeams($fields, $options);
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