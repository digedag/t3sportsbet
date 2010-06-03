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

//tx_rnbase::load('tx_t3sportsbet_mod1_decorator');



/**
 * Search match bets from database
 */
class tx_t3sportsbet_mod1_lister_MatchBet {
	private $mod;
	private $data;
	private $SEARCH_SETTINGS;
	private $betsetUid;
	private $matchUids;
	
	public function __construct(&$mod, $options = array()) {
		$this->init($mod, $options);
	}

	private function init($mod, $options) {
		$this->options = $options;
		$this->mod = $mod;
		$this->formTool = $mod->getFormTool();
		$this->resultSize = 0;
		$this->data = t3lib_div::_GP('searchdata');

		if(!isset($options['nopersist']))
			$this->SEARCH_SETTINGS = t3lib_BEfunc::getModuleData(array ('searchterm' => ''),$this->data,$this->mod->MCONF['name'] );
		else
			$this->SEARCH_SETTINGS = $this->data;
	}
	public function setMatchUids($uids) {
		$this->matchUids = $uids;
	}
	public function setBetSetUid($uid) {
		$this->betsetUid = $uid;
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
		return $out;
	}
	public function getResultList() {
		$content = '';

		$pager = tx_rnbase::makeInstance('tx_rnbase_util_BEPager', 'matchBetPager', $this->getModule()->getName(), $this->getModule()->getPid());
		$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();


		// Set options
		$options = array('count'=>1);

		$fields = array();		
		// Set filter		
		if ($this->betsetUid)
			$fields['BET.BETSET'] = array(OP_EQ_INT => $this->betsetUid);
		if ($this->matchUids)
			$fields['BET.T3MATCH'] = array(OP_IN_INT => $this->matchUids);

		$options['orderby']['BET.TSTAMP'] = 'desc';
		$cnt = $srv->searchBet($fields, $options);
		unset($options['count']);
		$pager->setListSize($cnt);
		$pager->setOptions($options);

		$items = $srv->searchBet($fields, $options);
		$ret = array();

		$ret['table'] = $this->showBets($items);
		$ret['totalsize'] = $cnt;
		$pagerData = $pager->render();
		$ret['pager'] .= '<div class="pager">' . $pagerData['limits'] . ' - ' .$pagerData['pages'] .'</div>';
		return $ret;
		
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

	private function showBets($bets) {
		tx_rnbase::load('tx_t3sportsbet_mod1_decorator');
		$decor = tx_rnbase::makeInstance('tx_t3sportsbet_util_BetDecorator');
		$decor->setFormTool($this->formTool);
		$columns = array(
			'uid' => array('decorator' => $decor, 'title' => 'label_uid'),
			'tstamp' => array('decorator' => $decor, 'title' => 'label_tstamp'),
			't3match' => array('decorator' => $decor, 'title' => 'label_match'),
			't3matchresult' => array('decorator' => $decor, 'title' => 'label_result'),
			'bet' => array('decorator' => $decor, 'title' => 'label_bet'),
			'points' => array('decorator' => $decor, 'title' => 'label_points'),
			'fe_user' => array('decorator' => $decor, 'title' => 'label_feuser'),
		);
		if($bets) {
			$arr = tx_t3sportsbet_mod1_decorator::prepareRecords($bets, $columns, $this->formTool, $this->options);
			$out .= $this->mod->doc->table($arr[0]); //, $this->getTableLayout()
		}
		else {
	  	$out = '<p><strong>'.$GLOBALS['LANG']->getLL('msg_no_bets_found').'</strong></p><br/>';
		}
		return $out;
	}

	/**
	 * @return tx_rnbase_mod_IModule
	 */
	private function getModule() {
		return $this->mod;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/lister/class.tx_t3sportsbet_mod1_lister_MatchBet.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/lister/mod1/class.tx_t3sportsbet_mod1_lister_MatchBet.php']);
}
?>