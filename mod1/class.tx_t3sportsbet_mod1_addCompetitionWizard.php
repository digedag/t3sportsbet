<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (rene@system25.de)
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
tx_div::load('tx_rnbase_util_Misc');

/**
 * Die Klasse zeigt Objekte im Backend an.
 */
class tx_t3sportsbet_mod1_addCompetitionWizard {

	/**
	 * Handle the wizard
	 *
	 * @param tx_t3sportsbet_mod1_index $mod
	 * @param tx_rnbase_util_formTool $formTool
	 * @param tx_t3sportsbet_models_betgame $betgame
	 * @return string
	 */
	function handleRequest(&$mod, $betgame) {
		$this->mod = $mod;
		$this->doc = $mod->doc;
		$this->formTool = $mod->formTool;
		$comp2set = strlen(t3lib_div::_GP('comp2betset')) > 0; // Wurde der Submit-Button gedrückt?
		$out = '';
		if($comp2set) {
			$out .= $this->handleCompetition2Betgame($betgame);
		}
		else {
			$out .= $this->showInfoPage($betgame);
		}
		return $out;
	}
	/**
	 * Zeigt die Infoseite mit den möglichen Optionen
	 *
	 * @param tx_t3sportsbet_models_betgame $betgame
	 * @return string
	 */
	private function showInfoPage(&$betgame) {
		$out .= $this->doc->section($GLOBALS['LANG']->getLL('label_info').':',$GLOBALS['LANG']->getLL('msg_add_competition'), 0, 1,ICON_INFO);
		$out .= $this->doc->spacer(15);
		
		$menuData = array();
		$comps = $betgame->getCompetitions();

		if(!count($comps)) {
			$out .= $this->doc->section($GLOBALS['LANG']->getLL('label_info').':',$GLOBALS['LANG']->getLL('msg_no_competition_in_betgame'), 0, 1,ICON_WARN);
			$out .= $this->doc->spacer(15);
			$options['title'] = $GLOBALS['LANG']->getLL('label_editbetgame');
			$out .= $this->formTool->createEditButton('tx_t3sportsbet_betgames', $betgame->uid,$options);
			
		}
		else {
			$menu = $this->getCompMenu($comps);
			$out .= $menu['menu'];
			$out .= $this->formTool->createSubmit('comp2betset', $GLOBALS['LANG']->getLL('label_join_competition'), $GLOBALS['LANG']->getLL('msg_join_competition'));
		}
//		t3lib_div::debug($betgame->getCompetitions(), 'tx_t3sportsbet_mod1_addCompetitionWizard'); // TODO: remove me

//		$out .= $this->handleCompetition2Betgame($searcher->getCompetition());
		$params['params'] = '&betgame='.$betgame->uid;
		$params['params'] .= '&round='.($betgame->getBetSetSize()+1);
		$params['title'] = $GLOBALS['LANG']->getLL('label_create_betset');
		$out .= $this->doc->spacer(15);
		$out .= $this->formTool->createNewButton('tx_t3sportsbet_betsets', $this->mod->id,$params);
		
		return $out;
	}
	/**
	 * Erstellt aus dem aktuellen Wettbewerb die notwendigen Tiprunden
	 *
	 * @param tx_t3sportsbet_models_betgame $betgame
	 * @return string
	 */
	private function handleCompetition2Betgame($betgame) {
		$menu = $this->getCompMenu($betgame->getCompetitions());
		$compId = $menu['value'];
		$matches = $this->loadMatches($compId);
		if(!count($matches)) return $this->doc->section($GLOBALS['LANG']->getLL('label_info').':',$GLOBALS['LANG']->getLL('msg_no_matchs_found'), 0, 1,ICON_WARN);

		$lastRound = -1;
		foreach($matches as $match) {
			$round = intval($match->record['round']);
			if($lastRound != $round) {
				$uid = $round;
				$lastRound = $round;
			}
			// Alle UIDs einer Runde sammeln
			$rounds[$round][] = $match->uid;
		}
		// Jetzt das Datenarray anlegen
		$data = array();
		foreach($rounds As $key => $matchUids) {
			$data['tx_t3sportsbet_betsets']['NEW'.$key]['pid'] = $betgame->getPid();
			$data['tx_t3sportsbet_betsets']['NEW'.$key]['betgame'] = $betgame->uid;
			$data['tx_t3sportsbet_betsets']['NEW'.$key]['t3matches'] = 'tx_cfcleague_games_'.implode(',tx_cfcleague_games_',$matchUids);
			$data['tx_t3sportsbet_betsets']['NEW'.$key]['status'] = 0;
			$data['tx_t3sportsbet_betsets']['NEW'.$key]['round'] = $key;
			$data['tx_t3sportsbet_betsets']['NEW'.$key]['round_name'] = $key . $GLOBALS['LANG']->getLL('label_roundnamedefault');
		}
		$tce =& tx_rnbase_util_DB::getTCEmain($data);
		$tce->process_datamap();
		$out .= $GLOBALS['LANG']->getLL('msg_add_competition_finished');
//		t3lib_div::debug($data, 'tx_t3sportsbet_mod1_addCompetitionWizard'); // TODO: remove me
			return (strlen($out)) ? $this->mod->doc->section($GLOBALS['LANG']->getLL('label_info').':',$out, 0, 1,ICON_INFO) : '';
	}

	private function loadMatches($compId) {
		$matchTable = tx_div::makeInstance('tx_cfcleaguefe_util_MatchTable');
		$matchTable->setCompetitions($compId);
		$matchTable->setIgnoreDummy();
		$fields = array();
		$options = array();
		$options['orderby']['MATCH.ROUND'] = 'ASC';
		$options['orderby']['MATCH.DATE'] = 'ASC';
		$matchTable->getFields($fields, $options);
		$service = tx_cfcleaguefe_util_ServiceRegistry::getMatchService();
		return $service->search($fields, $options);
	}
	private function getCompMenu($comps) {
		foreach($comps As $comp)
			$menuData[$comp->uid] = $comp->getName();
		return $this->formTool->showMenu($this->mod->id, 'bettools', $this->mod->MCONF['name'],$menuData);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_addCompetitionWizard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_addCompetitionWizard.php']);
}


?>
