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
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Manage TeamBets.
 */
class tx_t3sportsbet_mod1_addTeamBets {
	var $mod;
	public function __construct($mod) {
		$this->mod = $mod;
	}
	/**
	 * Ausführung des Requests
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	public function showScreen($currBetSet) {
		$out .= $this->handleResetTeamBets($currBetSet);
		$out .= $this->handleShowTeamBets($currBetSet);
		$options = array();
		$options['title'] = $GLOBALS['LANG']->getLL('label_btn_newteambet');
		$options['params'] = '&defVals[tx_t3sportsbet_teamquestions][betset]=tx_t3sportsbet_betsets_'.$currBetSet->getUid();
		$options['linker'][] = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_link_TeamBets');

		$lister = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_lister_TeamQuestion', $this->mod, $options);
		$lister->setBetSetUid($currBetSet->getUid());

		$list = $lister->getResultList();
		$out .= $list['pager']."\n".$list['table'];
		$out .= $this->getFormTool()->createNewButton('tx_t3sportsbet_teamquestions', $currBetSet->record['pid'], $options);

		return $out;
	}

	/**
	 * 
	 * @param array $options
	 * @return tx_t3sportsbet_mod1_lister_TeamBet
	 */
	private function getTeamBetLister($options) {
		$lister = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_lister_TeamBet', $this->mod, $options);
		return $lister;
	}
	/**
	 * Show a list of bets for a team question
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	private function handleShowTeamBets($currBetSet) {

		$teamQuestionUid = $this->getFormTool()->getStoredRequestData('showTeamBets', array(), $this->mod->MCONF['name']);
		if($teamQuestionUid == 0) return '';

		// Gehört die Question zum aktuellen Betset
		$teamQuestion = tx_rnbase::makeInstance('tx_t3sportsbet_models_teamquestion', $teamQuestionUid);
		if($teamQuestion->getBetSetUid() != $currBetSet->getUid()) return '';

		$options['module'] = $this->mod;
		$lister = $this->getTeamBetLister($options);
		$lister->setTeamQuestionUid($teamQuestionUid);

		$list = $lister->getResultList();
		$out .= $list['pager']."\n".$list['table'];
		$out .= $this->getFormTool()->createSubmit('showTeamBets[0]', $GLOBALS['LANG']->getLL('label_close'));
		if(!$currBetSet->isFinished())
			$out .= $this->getFormTool()->createSubmit('resetTeamBets['.$teamQuestion->uid.']', $GLOBALS['LANG']->getLL('label_resetbets'), $GLOBALS['LANG']->getLL('msg_resetbets'));

		$out .=  '<hr />';
		$headline = strip_tags($teamQuestion->getQuestion());
		return $this->mod->doc->section($headline,$out,0,1,ICON_INFO);
	}

	/**
	 * Show a list of bets for a team question
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	private function handleResetTeamBets($currBetSet) {
		$data = t3lib_div::_GP('resetTeamBets');
		if(!is_array($data)) return '';
		list($itemid, ) = each($data);
		tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->resetTeamBets($itemid);
	}
	
	/**
	 * Liefert das FormTool
	 *
	 * @return tx_rnbase_util_FormTool
	 */
	private function getFormTool() {
		return $this->mod->formTool;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_addTeamBets.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_addTeamBets.php']);
}

?>