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


/**
 * Search matches from competitions
 * We to it by showing to select boxes: one for competition and the other for round
 */
class tx_t3sportsbet_mod1_matchsearcher {
	private $mod;
	private $data;
	private $SEARCH_SETTINGS;
	/** @var tx_cfcleague_selector */
	private $selector;

	public function __construct(&$mod, $options = array()) {
		$this->init($mod, $options);
	}

	/**
	 * 
	 * @param unknown_type $mod
	 * @param array $options
	 */
	private function init($mod, $options) {
		$this->options = $options;
		$this->mod = $mod;
		$this->options['pid'] = $this->mod->id;
		$this->formTool = $this->mod->formTool;
		$this->resultSize = 0;
		$this->data = t3lib_div::_GP('searchdata');
		$this->competitions = $options['competitions'];

		$this->selector = t3lib_div::makeInstance('tx_cfcleague_selector');
		$this->selector->init($mod->doc, $mod->MCONF['name']);
		if(!isset($options['nopersist']))
			$this->SEARCH_SETTINGS = t3lib_BEfunc::getModuleData(array ('searchterm' => ''),$this->data,$this->mod->MCONF['name'] );
		else
			$this->SEARCH_SETTINGS = $this->data;
			
	}
	/**
	 * Liefert das Suchformular. Hier die beiden Selectboxen anzeigen
	 *
	 * @param string $label Alternatives Label
	 * @return string
	 */
	public function getSearchForm($label = '') {
    global $LANG;
    $out = '';
    // Wir zeigen zwei Selectboxen an
    $this->currComp = $this->selector->showLeagueSelector($out,$this->mod->id, $this->competitions);
    if(!$this->currComp) {
      return $out . $this->mod->doc->section('Info:',$LANG->getLL('msg_no_competition_in_betgame'),0,1,ICON_WARN);
    }
//    $out.=$this->mod->doc->spacer(5);
    
    $rounds = $this->currComp->getRounds();
		if(!count($rounds)){
			$out .= $LANG->getLL('msg_no_round_in_competition');
			return $out;
		}
		// Jetzt den Spieltag wählen lassen
		$this->current_round = $this->selector->showRoundSelector($out,$this->mod->id,$this->currComp);
		$out .= '<div style="clear:both" />';
		//t3lib_div::debug($this->currRound, 'tx_t3sportsbet_mod1_matchsearcher'); // TODO: Remove me!
		return $out;
	}
	public function getResultList() {
		$content = '';
		if(!is_object($this->currComp)) return '';

		// Mit Matchtable nach Spielen suchen
		$matchTable = $this->getMatchTable();
		$matchTable->setCompetitions($this->currComp->uid);
		$matchTable->setRounds($this->current_round->uid);
		if(isset($this->options['ignoreDummies']))
			$matchTable->setIgnoreDummy();
		$fields = array();
		$options = array();
		$options['orderby']['MATCH.DATE'] = 'ASC';
		$matchTable->getFields($fields, $options);
		$service = tx_cfcleaguefe_util_ServiceRegistry::getMatchService();
		$matches = $service->search($fields, $options);
		$this->resultSize = count($matches);
		$label = $this->resultSize . ' ' . (($this->resultSize == 1) ? $GLOBALS['LANG']->getLL('msg_found_match') : $GLOBALS['LANG']->getLL('msg_found_matches'));
		$this->showMatches($content, $label, $matches);
		
		return $content;
	}
	/**
	 * Liefert die Anzahl der gefunden Datensätze.
	 * Funktioniert natürlich erst, nachdem die Ergebnisliste abgerufen wurde.
	 *
	 * @return int
	 */
	public function getSize() {
		return $this->resultSize;		
	}

	function showMatches(&$content, $headline, &$matches) {
		tx_rnbase::load('tx_rnbase_mod_Tables');
		$decor = tx_rnbase::makeInstance('tx_t3sportsbet_util_MatchDecorator', $this->mod);
		$columns = array(
			'uid' => array('title' => 'label_uid', 'decorator' => $decor),
			'date' => array('title' => 'tx_cfcleague_games.date', 'decorator' => $decor),
			'home' => array('title' => 'tx_cfcleague_games.home', 'method' => 'getHomeNameShort'),
			'guest' => array('title' => 'tx_cfcleague_games.guest', 'method' => 'getGuestNameShort'),
			'competition' => array('title' => 'label_group', 'decorator' => $decor),
			'status' => array('title' => 'tx_cfcleague_games.status', 'method' => 'getStateName'),
			'result' => array('method' => 'getResult', 'title' => 'label_result'),
		);

		if($matches) {
			global $LANG;
			$tableName = isset($options['tablename']) ? $options['tablename'] : 'tx_cfcleague_games';
			$LANG->includeLLFile('EXT:cfc_league/locallang_db.xml');
			tx_rnbase::load('tx_rnbase_mod_Tables');
			$arr = tx_rnbase_mod_Tables::prepareTable($matches, $columns, $this->formTool, $this->options);
			$out .= $this->mod->doc->table($arr[0]);
		}
		else {
	  	$out = '<p><strong>'.$GLOBALS['LANG']->getLL('msg_no_matches_in_betset').'</strong></p><br/>';
		}
		$content .= $this->mod->doc->section($headline.':',$out,0,1,ICON_INFO);
	}

	/**
	 * Returns an instance of tx_cfcleaguefe_util_MatchTable
	 * @return tx_cfcleaguefe_util_MatchTable
	 */
	private function getMatchTable() {
		return tx_rnbase::makeInstance('tx_cfcleaguefe_util_MatchTable');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_matchsearcher.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_matchsearcher.php']);
}
?>