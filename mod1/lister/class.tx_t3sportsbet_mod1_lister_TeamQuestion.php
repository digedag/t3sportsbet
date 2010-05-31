<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 René Nitzsche (rene@system25.de)
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
 * List team bets
 */
class tx_t3sportsbet_mod1_lister_TeamQuestion {
	private $mod;
	private $data;
	private $SEARCH_SETTINGS;
	private $betsetUid;
	
	/**
	 * Constructor
	 * 
	 * @param 	tx_rnbase_mod_IModule $mod
	 * @param 	array $options
	 */
	public function __construct($mod, $options = array()) {
		$this->init($mod, $options);
	}

	/**
	 * Init object
	 *
	 * @param 	tx_rnbase_mod_IModule $mod
	 * @param 	array $options
	 */
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
	/**
	 * TODO: Returns the complete search form
	 */
	public function getSearchForm() {
		
		return $out;
	}
	/**
	 * @return tx_rnbase_mod_IModule
	 */
	private function getModule() {
		return $this->mod;
	}

	public function setBetsetUid($uid) {
		$this->betsetUid = $uid;
	}
	/**
	 * 
	 */
	public function getResultList() {
		$pager = tx_rnbase::makeInstance('tx_rnbase_util_BEPager', 'jobAdPager', $this->getMod()->getName(), 0);
		// Get company service
		$srv = tx_t3sportsbet_util_serviceRegistry::getTeamBetService();
		
		// Set options
		$options = array('count'=>1);

		$fields = array();		
		// Set filter		
		if ($this->betsetUid)
			$fields['TEAMQUESTION.BETSET'] = array(OP_EQ_INT => $this->betsetUid);

		// Set more options
		$options['orderby']['TEAMQUESTION.SORTING'] = 'ASC';
//		$options['enablefieldsfe'] = 1;

		// Get counted data
		$cnt = $srv->searchTeamQuestion($fields, $options);
		unset($options['count']);
		$pager->setListSize($cnt);
		$pager->setOptions($options);

		// Get data
		$items = $srv->searchTeamQuestion($fields, $options);
		$ret = array();
		$content = '';
		$this->showTeamQuestions($content, $items);
		$ret['table'] = $content;
		$ret['totalsize'] = $cnt;
		$pagerData = $pager->render();
		$ret['pager'] .= '<div class="pager">' . $pagerData['limits'] . ' - ' .$pagerData['pages'] .'</div>';
		return $ret;
	}

	/**
	 * Start creation of result list
	 * @param string $content
	 * @param array $items
	 */
	private function showTeamQuestions(&$content, $items) {
		$decor = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_decorator_TeamQuestion', $this->getModule());
		$columns = array(
			'uid' => array('title' => 'label_uid', 'decorator' => $decor),
			'question' => array('title' => 'label_question'),
			'points' => array('title' => 'label_points'),
			'openuntil' => array('title' => 'label_openuntil'),
		);

		if($items) {
			tx_rnbase::load('tx_rnbase_mod_Tables');
			$arr = tx_rnbase_mod_Tables::prepareTable($items, $columns, $this->formTool, $this->options);
			$out = $this->mod->doc->table($arr[0]);
		}
		else {
	  	$out = '<p><strong>###LABEL_MSG_NO_ITEMS_FOUND###</strong></p><br/>';
		}
		$content .= $out;
	}

	/**
	 * Method to get the number of data records
	 * Works only if the result list has been retrieved
	 *
	 * @return 	int
	 */
	public function getSize() {
		return $this->resultSize;		
	}	

	
	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 * 
	 * @return 	tx_rnbase_mod_IModule
	 */
	private function getMod() {
		return $this->mod;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/beutil/class.tx_t3sportsbet_mod1_lister_TeamQuestion.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/beutil/class.tx_t3sportsbet_mod1_lister_TeamQuestion.php']);
}
?>