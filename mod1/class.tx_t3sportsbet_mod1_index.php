<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche <rene@system25.de>
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

require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once(PATH_t3lib.'class.t3lib_extfilefunc.php');

require_once(t3lib_extMgm::extPath('cfc_league').'class.tx_cfcleague.php');

tx_div::load('tx_cfcleague_mod1_decorator');

// Mögliche Icons im BE für die Funktion doc->icons()
define('ICON_OK', -1);
define('ICON_INFO', 1);
define('ICON_WARN', 2);
define('ICON_FATAL', 3);

/**
 * Module extension (addition to function menu) 'LMO Import' for the 'lmo2cfcleague' extension.
 *
 * @author	Rene Nitzsche <rene@system25.de>
 * @package	TYPO3
 * @subpackage	tx_lmo2cfcleague
 */
class tx_t3sportsbet_mod1_index extends t3lib_extobjbase {

	/**
	 * Returns the module menu
	 *
	 * @return	Array with menuitems
	 */
	function modMenu()	{
	  global $LANG;
		return Array (
//      "tx_lmo2cfcleague_modfunc1_check" => "",
		);
	}
	function init(&$pObj, $MCONF) {
		parent::init($pObj, $MCONF);
		$this->MCONF = $pObj->MCONF;
		$this->id = $pObj->id;
		$GLOBALS['LANG']->includeLLFile('EXT:t3sportsbet/mod1/locallang.xml');
		
//    $this->pObj->doc->form = '<form action="index.php" method="post" enctype="multipart/form-data" >';

	}

	/**
	 * Main method of the module
	 *
	 * @return	HTML
	 */
	function main()	{
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS, $IMPORT_FUNC;
/*
Vorgehen
--------
*/
    $this->doc = $this->pObj->doc;
		$this->formTool = tx_div::makeInstance('tx_rnbase_util_FormTool');
		$this->formTool->init($this->pObj->doc);
		$this->selector = tx_div::makeInstance('tx_t3sportsbet_mod1_selector');
		$this->selector->init($this->pObj->doc, $this->MCONF);

		// Anzeige der vorhandenen Ligen
		$currentGame = $this->selector->showGameSelector($content,$this->pObj->id);
		if(!$currentGame) {
			$content .= $this->doc->section('Info:',$LANG->getLL('msg_no_game_in_page'),0,1,ICON_WARN);
			$content .= '<p style="margin-top:5px; font-weight:bold;">'.$this->formTool->createNewLink('tx_t3sportsbet_betgames', $this->pObj->id,$LANG->getLL('msg_create_new_game')).'</p>';
			
			return $content;
		}
		$currentRound = $this->selector->showRoundSelector($content,$this->pObj->id, $currentGame);
		if(!$currentRound)
			return $content;

		$menu = $this->formTool->showTabMenu($this->id, 'bettools', $this->MCONF['name'],
				array('0' => $LANG->getLL('tab_control'), 
							'1' => $LANG->getLL('tab_addmatches'),
							'2' => $LANG->getLL('tab_bets')));
		$content .= $menu['menu'];
		$content .= $this->formTool->form->printNeededJSFunctions_top();
		$content .= '<div style="display: block; border: 1px solid #a2aab8;">';
		$content .= '</div>';

		$this->betset = $currentRound; // Nicht schön, aber so hat der Linker Zugriff
		$content .= $this->handleShowBets($currentRound);
		$content .= $this->handleResetBets($currentRound);
		$content .= $this->handleSaveBetSet($currentRound);
		$content .= $this->handleAnalyzeBets($currentGame);
		
		switch($menu['value']) {
			case 0:
				$content .= $this->showBetSet($currentRound);
				break;
			case 1:
				$clazzName = tx_div::makeInstanceClassname('tx_t3sportsbet_mod1_addMatches');
				$addMatches = new $clazzName($this);
				$content .= $addMatches->handleRequest($currentRound);
				break;
			case 2:
				$content .= $this->showBets($currentRound);
				break;
		}
		$content .= $this->formTool->form->printNeededJSFunctions();
		
		return $content;
	}
	/**
	 * Show a list of bets for a betset
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 */
	function showBets($currBetSet) {
		// Alle Tips für dieses Betset suchen
		$fields['BET.BETSET'][OP_EQ_INT] = $currBetSet->uid;
		$options['orderby']['BET.TSTAMP'] = 'desc';
		$options['count'] = 1;
		$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$cnt = $srv->searchBet($fields, $options);
		unset($options['count']);

		$pageTSconfig = t3lib_BEfunc::getPagesTSconfig($this->id);
		$maxRecords = (is_array($pageTSconfig) && is_array($pageTSconfig['tx_t3sportsbet.']['betlistCfg.'])) ?
		  intval($pageTSconfig['tx_t3sportsbet.']['betlistCfg.']['maxRecords']) : 300;
		$options['limit'] = $maxRecords;
		$bets = $srv->searchBet($fields, $options);
		$options = array();
		$options['module'] = $this;
		$searcher = $this->getBetSearcher($options);
		$out .= $searcher->showBets($GLOBALS['LANG']->getLL('label_betlist'), $bets);
		$out .= $GLOBALS['LANG']->getLL('label_betcount') .': ' . $cnt;
		$out .= '<br/>'.sprintf($GLOBALS['LANG']->getLL('msg_betcountmax'), $maxRecords);
		
		return $out;
	}
	/**
	 * Show a list of bets for a match
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 */
	function handleShowBets($currBetSet) {
		$matchUids = t3lib_div::_GP('showBets');
		if(!is_array($matchUids)) return;

		$options['module'] = $this;
		$searcher = $this->getBetSearcher($options);
		$service = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$clazzname = tx_div::makeInstanceClassname('tx_cfcleaguefe_models_match');
		$matchUids = array_keys($matchUids);
		$out = '';
		foreach($matchUids As $uid) {
			$match = new $clazzname($uid);
			$bets = $service->getBets($currBetSet, $match);
			$out .= $searcher->showBets($GLOBALS['LANG']->getLL('label_betlist'), $bets);
		}
		return $out;
	}
	/**
	 * Reset all bets for a given match.
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 */
	function handleResetBets($currBetSet) {
		$matchUids = t3lib_div::_GP('resetBets');
		if(!is_array($matchUids)) return;

		$tce =& tx_rnbase_util_DB::getTCEmain();
		$details = 'T3sportsbet: All bets for match with uid %s of betset with uid %s were reset.';
		$matchUids = array_keys($matchUids);
		foreach($matchUids As $uid) {
			// Jetzt alle Tips für das Spiel suchen in dieser Tiprunde suchen und zurücksetzen
			$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
			$srv->resetBets($currBetSet, $uid);
			
			//$tce->BE_USER->writelog($type,$action,$error,$details_nr,$details,$data,$table,$recuid,$recpid,$event_pid,$NEWid);
			$data = array($uid, $currBetSet->uid);
			$tce->BE_USER->writelog(1,2,0,0,$details,$data);
		}
	}

	/**
	 * Show form to add matches to betset
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	function handleSaveBetSet($currBetSet) {
		$out = '';
		$button = strlen(t3lib_div::_GP('savebetset')) > 0;
		if($button) {
			$data = t3lib_div::_GP('data');
			$tce =& tx_rnbase_util_DB::getTCEmain($data);
			$tce->process_datamap();
			$out .= $GLOBALS['LANG']->getLL('msg_betset_saved');
			$currBetSet->reset();
		}
		return $out;
	}
	/**
	 * Starts analysation of betgame if button was pressed.
	 * @param tx_t3sportsbet_models_betgame $betGame
	 * @return string
	 */
	function handleAnalyzeBets($betGame) {
		//
		$out = '';
		$button = strlen(t3lib_div::_GP('analyzebets')) > 0;
		if($button) {
			$service = tx_t3sportsbet_util_serviceRegistry::getBetService();
			$ret = $service->analyzeBets($betGame);
			$out .= $GLOBALS['LANG']->getLL('msg_bets_finished') . ':' . $ret;
		}
		return $out;
	}


	/**
	 * Show betset
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	function showBetSet($currBetSet) {
		$matches = $currBetSet->getMatches();
		$options['linker'][] = tx_div::makeInstance('tx_t3sportsbet_mod1_MatchEditLink');
		$options['module'] = $this;
		$searcher = $this->getMatchSearcher($options);
		$searcher->showMatches($out, $GLOBALS['LANG']->getLL('label_matchlist'), $currBetSet->getMatches());
		$this->formTool->addTCEfield2Stack('tx_t3sportsbet_betsets', $currBetSet->record, 'status','<strong>'.$GLOBALS['LANG']->getLL('label_change_state') . ':</strong>&nbsp;');
		$arr = $this->formTool->getTCEfields('editform');
		$out .= implode('',$arr);
  	$out .= $this->formTool->createSubmit('savebetset',$GLOBALS['LANG']->getLL('label_save'));
		$out .= $this->pObj->doc->spacer(10);
  	$out .= '<p>'.$this->formTool->createSubmit('analyzebets',$GLOBALS['LANG']->getLL('label_analyzebets')).'</p>';
		return $out;
	}

	/**
	 * Get a match searcher
	 *
	 * @param array $options
	 * @return tx_t3sportsbet_mod1_matchsearcher
	 */
	function getMatchSearcher(&$options) {
		$clazz = tx_div::makeInstanceClassname('tx_t3sportsbet_mod1_matchsearcher');
		$searcher = new $clazz($this, $options);
		return $searcher;
	}
	/**
	 * Get a bet searcher
	 *
	 * @param array $options
	 * @return tx_t3sportsbet_mod1_betsearcher
	 */
	function getBetSearcher(&$options) {
		$clazz = tx_div::makeInstanceClassname('tx_t3sportsbet_mod1_betsearcher');
		$searcher = new $clazz($this, $options);
		return $searcher;
	}
}

class tx_t3sportsbet_mod1_MatchEditLink implements tx_cfcleague_mod1_Linker {
	/**
	 * Bearbeitung von Spielen
	 *
	 * @param tx_cfcleaguefe_models_match $match
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param int $currentPid
	 * @param array $options
	 * @return string
	 */
	function makeLink($match, $formTool, $currentPid, $options) {
		$out = $formTool->createEditLink('tx_cfcleague_games', $match->uid, $GLOBALS['LANG']->getLL('label_edit'));
		if(isset($options['module'])) {
			$out .= '<br />';
			$mod = $options['module'];
			$betset = $mod->betset;
			$cnt = $betset->getBetCount($match);
			if($cnt)
				$out .= $formTool->createSubmit('showBets['.$match->uid.']', $GLOBALS['LANG']->getLL('label_showbets') . ' (' . $cnt. ')');
//			$out .= $GLOBALS['LANG']->getLL('label_numberOfBets').': ' . $cnt;
			// Wenn das Spiel ausgewertet wurde und die Tiprunde noch offen ist
			if(!$betset->isFinished() && ($betset->getMatchState($match) == 'FINISHED' ))
				$out .= '<br />'.$formTool->createSubmit('resetBets['.$match->uid.']', $GLOBALS['LANG']->getLL('label_resetbets'), $GLOBALS['LANG']->getLL('msg_resetbets'));
		}

		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_index.php']);
}
?>