<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2016 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Spiele einer Tiprunde hinzufügen.
 */
class tx_t3sportsbet_mod1_addMatches {
	var $mod;
	public function tx_t3sportsbet_mod1_addMatches(&$mod) {
		$this->mod = $mod;
	}
	/**
	 * Ausführung des Requests
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	public function handleRequest(&$currBetSet) {
		$out .= $this->handleAddCompetition();
		$competitions = $currBetSet->getBetgame()->getCompetitions();
		if(!count($competitions)) {
			$out .= $this->handleNoCompetitions($currBetSet);
		}
		else {
			$out .= $this->handleAddMatches($currBetSet);
			$out .= $this->showAddMatches($currBetSet, $competitions);
		}
		return $out;
	}
	/**
	 * Sollte aufgerufen werden, wenn keine Wettberbe im Tipspiel zugeordnet sind
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 */
	private function handleNoCompetitions($currBetSet) {
		$out .= $this->mod->doc->section('Info:',$GLOBALS['LANG']->getLL('msg_no_competition_in_betgame'),0,1,ICON_WARN);
		$out .= $this->mod->doc->spacer(15);
		$out .= $this->getFormTool()->form->getSoloField('tx_t3sportsbet_betgames',$currBetSet->getBetgame()->record,'competition');
		$out .= $this->getFormTool()->createSubmit('updateBetgame',$GLOBALS['LANG']->getLL('btn_update'));
		return $out;
	}
	/**
	 * Liefert das FormTool
	 *
	 * @return tx_rnbase_util_FormTool
	 */
	private function getFormTool() {
		return $this->mod->formTool;
	}

	/**
	 * Shows a list of matches
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @param array $competitions
	 * @return string
	 */
	protected function showAddMatches($currBetSet, $competitions) {

		tx_rnbase::load('tx_t3sportsbet_mod1_matchsearcher');
		$options['checkbox'] = 1;

		$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$matches = $srv->findMatchUids($currBetSet->getBetgame());
		foreach($matches As $match) {
			$options['dontcheck'][$match['uid']] = $GLOBALS['LANG']->getLL('msg_match_already_joined');
		}
		$options['competitions'] = $competitions;
		$options['ignoreDummies'] = 1;
		$searcher = $this->getMatchSearcher($options);
		$out .= $searcher->getSearchForm();
		$out .= $searcher->getResultList();
		if($searcher->getSize()) {
			// Button für Zuordnung
			$out .= $this->mod->formTool->createSubmit('match2betset', $GLOBALS['LANG']->getLL('label_join_matches'), $GLOBALS['LANG']->getLL('msg_join_matches'));
		}
		return $out;
	}
	/**
	 * Add matches to a betset
	 *
	 * @return string
	 */
	private function handleAddCompetition() {
//		$out = '';

		$buttonPressed = strlen(t3lib_div::_GP('updateBetgame')) > 0; // Wurde der Submit-Button gedrückt?
		if($buttonPressed) {
			$data = t3lib_div::_GP('data');
			$tce = tx_rnbase_util_DB::getTCEmain($data, array());
			$tce->process_datamap();
		}
	}
	/**
	 * Add matches to a betset
	 *
	 * @param tx_t3sportsbet_models_betset $currBetSet
	 * @return string
	 */
	private function handleAddMatches($currBetSet) {
		$out = '';
		$match2set = strlen(t3lib_div::_GP('match2betset')) > 0; // Wurde der Submit-Button gedrückt?
		if($match2set) {
			$matchUids = t3lib_div::_GP('checkEntry');
			if(!is_array($matchUids) || ! count($matchUids)) {
				$out = $GLOBALS['LANG']->getLL('msg_no_match_selected').'<br/>';
			}
			else {
				// Die Spiele setzen
				$service = tx_t3sportsbet_util_serviceRegistry::getBetService();
				$cnt = $service->addMatchesTCE($currBetSet, $matchUids);
				$out = $cnt.' '. $GLOBALS['LANG']->getLL('msg_matches_added');
			}
		}
		return (strlen($out)) ? $this->mod->doc->section($GLOBALS['LANG']->getLL('label_info').':',$out, 0, 1,ICON_INFO) : '';
	}

	/**
	 * Get a match searcher
	 *
	 * @param array $options
	 * @return tx_t3sportsbet_mod1_matchsearcher
	 */
	private function getMatchSearcher(&$options) {
		$searcher = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_matchsearcher', $this->mod, $options);
		return $searcher;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_addMatches.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_addMatches.php']);
}


?>
