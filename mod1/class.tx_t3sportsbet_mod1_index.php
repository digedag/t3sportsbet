<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Rene Nitzsche <rene@system25.de>
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

require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once(PATH_t3lib.'class.t3lib_extfilefunc.php');

require_once(t3lib_extMgm::extPath('cfc_league').'class.tx_cfcleague.php');

tx_rnbase::load('tx_cfcleague_mod1_decorator');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_t3sportsbet_mod1_handler_MatchMove');


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
//			"tx_lmo2cfcleague_modfunc1_check" => "",
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
		$this->formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$this->formTool->init($this->doc);
		$this->selector = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_selector');
		$this->selector->init($this->doc, $this->MCONF);

		$selector = '';
		// Anzeige der vorhandenen Tipspiele
		$currentGame = $this->selector->showGameSelector($selector,$this->pObj->id);
		if(!$currentGame) {
			$content .= $this->doc->section('Info:',$LANG->getLL('msg_no_game_in_page'),0,1,ICON_WARN);
			$content .= '<p style="margin-top:5px; font-weight:bold;">'.$this->formTool->createNewLink('tx_t3sportsbet_betgames', $this->pObj->id,$LANG->getLL('msg_create_new_game')).'</p>';
			return $content;
		}

		$currentRound = $this->selector->showRoundSelector($selector,$this->pObj->id, $currentGame);
		if(!$currentRound) {
			if(tx_rnbase_util_TYPO3::isTYPO42OrHigher())
				$this->pObj->subselector = $selector;
			else 
				$content .= '<div class="cfcleague_selector">'.$selector.'</div><div style="clear:both"/>';
			// Add competition wizard
			$wizard = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_addCompetitionWizard');
			$content .= $wizard->handleRequest($this, $currentGame);
			return $content;
		}
		if(tx_rnbase_util_TYPO3::isTYPO42OrHigher())
			$this->pObj->subselector = $selector;
		else 
			$content .= '<div class="cfcleague_selector">'.$selector.'</div><div style="clear:both"/>';

		// RequestHandler aufrufen.
		$content .= tx_t3sportsbet_mod1_handler_MatchMove::getInstance()->handleRequest($this);

		$menu = $this->formTool->showTabMenu($this->id, 'bettools', $this->MCONF['name'],
				array('0' => $LANG->getLL('tab_control'), 
							'1' => $LANG->getLL('tab_addmatches'),
							'2' => $LANG->getLL('tab_addteambets'),
							'3' => $LANG->getLL('tab_bets')));
		$content .= $menu['menu'];
		$content .= $this->formTool->form->printNeededJSFunctions_top();
		$content .= '<div style="display: block; border: 1px solid #a2aab8; clear:both;"></div>';

		try {
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
					$handler = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_addMatches', $this);
					$content .= $handler->handleRequest($currentRound);
					break;
				case 2:
					$handler = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_addTeamBets', $this);
					$content .= $handler->showScreen($currentRound);
					break;
				case 3:
					$content .= $this->showBets($currentRound);
					break;
			}
			$content .= $this->showInfobar($currentRound);
		} catch (Exception $e) {
			$msg = '<h2>FATAL ERROR: </h2><pre>';
//			$e->getMessage();
			$msg .= $e->__toString();
			$msg .= '</pre>';
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::warn('Exception in BE module.', 't3sportsbet', array('Exception' => $e->getMessage()));
			$content .= $msg;
		}
		
		$content .= $this->formTool->form->printNeededJSFunctions();
		
		return $content;
	}
	/**
	 * @return tx_t3sportsbet_models_betset
	 */
	public function getCurrentBetset() {
		return $this->betset;
	}
	/**
	 * Show a list of all bets for a betset
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 */
	function showBets($currBetSet) {
		// Alle Tips für dieses Betset suchen
		$lister = $this->getBetSearcher($options);
		$lister->setBetSetUid($currBetSet->getUid());
		$list = $lister->getResultList();
		$out .= $list['pager']."\n".$list['table'];

		return $this->doc->section($GLOBALS['LANG']->getLL('label_betlist').':',$out,0,1,ICON_INFO);
	}
	/**
	 * Show a list of bets for a match
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 */
	function handleShowBets($currBetSet) {
		$matchUids = $this->getFormTool()->getStoredRequestData('showBets', array(), $this->getName());
		if($matchUids == 0) return '';
		
//		$matchUids = t3lib_div::_GP('showBets');
//		if(!is_array($matchUids)) return;

		$options['module'] = $this;
		$lister = $this->getBetSearcher($options);
		$service = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$lister->setMatchUids($matchUids);
		$lister->setBetSetUid($currBetSet->getUid());

		$list = $lister->getResultList();
		$out .= $list['pager']."\n".$list['table'];
		$out .= $this->getFormTool()->createSubmit('showBets[0]', $GLOBALS['LANG']->getLL('label_close'));
		return $this->doc->section($GLOBALS['LANG']->getLL('label_betlist').':',$out,0,1,ICON_INFO);
		
		
		$out = '';
		foreach($matchUids As $uid) {
			$match = tx_rnbase::makeInstance('tx_cfcleaguefe_models_match', $uid);
			$bets = $service->getBets($currBetSet, $match);
			$out .= $searcher->showBets($GLOBALS['LANG']->getLL('label_betlist'), $bets);
		}
//		return $this->doc->section($GLOBALS['LANG']->getLL('label_betlist').':',$out,0,1,ICON_INFO);
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
			$betsUpdated = tx_t3sportsbet_util_serviceRegistry::getBetService()->analyzeBets($betGame);
			$betsUpdated += tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->analyzeBets($betGame);
			$out .= $GLOBALS['LANG']->getLL('msg_bets_finished') . ':' . $betsUpdated;
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
		$options['linker'][] = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_link_MatchBets');
		$options['module'] = $this;

		$pasteButton = tx_t3sportsbet_mod1_handler_MatchMove::getInstance()->makePasteButton($currBetSet, $this);
		if($pasteButton)
			$out .= $this->doc->section('Info:',$pasteButton,0,1,ICON_INFO);
		
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
	 * Shows some information about current betset
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	public function showInfoBar($currBetSet) {
		$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$dates = $srv->getBetsetDateRange($currBetSet);
		if(!$dates) return '';
		$matchCnt = count($currBetSet->getMatches());
		$row[] = array($GLOBALS['LANG']->getLL('label_betsetinfo'));
		$date = $dates['low'][0];
		$match = $dates['low'][1];
		$row[] = array($GLOBALS['LANG']->getLL('label_betsetinfo_lowdate'), strftime('%d. %b %y %H:%M', $date) . ' (' . $match->getHomeNameShort() . '-' . $match->getGuestNameShort() .')');
		$date = $dates['high'][0];
		$match = $dates['high'][1];
		$row[] = array($GLOBALS['LANG']->getLL('label_betsetinfo_highdate'), strftime('%d. %b %y %H:%M', $date) . ' (' . $match->getHomeNameShort() . '-' . $match->getGuestNameShort() .')');
		if($dates['next']) {
			$date = $dates['next'][0];
			$match = $dates['next'][1];
			$row[] = array($GLOBALS['LANG']->getLL('label_betsetinfo_nextdate'), strftime('%d. %b %y %H:%M', $date) . ' (' . $match->getHomeNameShort() . '-' . $match->getGuestNameShort() .')');
		}
		$row[] = array($GLOBALS['LANG']->getLL('label_betsetinfo_matchcount'), $matchCnt);
		$row[] = array($GLOBALS['LANG']->getLL('label_betsetinfo_usercount'), $srv->getResultSize($currBetSet->uid));
		$row[] = array($GLOBALS['LANG']->getLL('label_betsetinfo_betcount'), $srv->getBetSize($currBetSet));
		$out .= $this->doc->table($row, $this->getTableLayout());
		
		return $out;
	}

	/**
	 * Get a match searcher
	 *
	 * @param array $options
	 * @return tx_t3sportsbet_mod1_matchsearcher
	 */
	function getMatchSearcher(&$options) {
		$searcher = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_matchsearcher', $this, $options);
		return $searcher;
	}
	/**
	 * Get a bet searcher
	 *
	 * @param array $options
	 * @return tx_t3sportsbet_mod1_lister_MatchBet
	 */
	function getBetSearcher(&$options) {
		$searcher = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_lister_MatchBet', $this, $options);
		return $searcher;
	}
	/**
	 * Liefert das Layout für die Infotabelle
	 *
	 * @return array
	 */
  function getTableLayout() {
		$layout = Array (
			'table' => Array('<table class="typo3-dblist" cellspacing="0" cellpadding="0" border="0">', '</table><br/>'),
			'0' => Array( // Format für 1. Zeile
					'tr'		=> Array('<tr class="c-headLineTable">','</tr>'),
					'defCol' => (tx_rnbase_util_TYPO3::isTYPO42OrHigher() ? Array('<td colspan="2">','</td>') : Array('<td colspan="2" class="c-headLineTable" style="font-weight:bold; color:white;">','</td>'))  // Format für jede Spalte in der 1. Zeile
			),
			'defRow' => Array ( // Formate für alle Zeilen
				'0' => Array('<td valign="top"  class="c-headLineTable" style="padding:2px 5px;">','</td>'), // Format für 1. Spalte in jeder Zeile
				'defCol' => Array('<td valign="top" style="padding:0 5px;">','</td>') // Format für jede Spalte in jeder Zeile
			),
		);
		return $layout;
  }
  /**
   * @return tx_rnbase_util_FormTool
   */
  public function getFormTool() {
  	return $this->formTool;
  }
  public function getName() {
  	return $this->MCONF['name'];
  }
  public function getPid() {
  	return $this->id;
  }
  public function getDoc() {
  	return $this->doc;
  }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_index.php']);
}
?>