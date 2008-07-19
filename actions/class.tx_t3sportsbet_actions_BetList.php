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

tx_div::load('tx_rnbase_action_BaseIOC');
tx_div::load('tx_t3sportsbet_models_betgame');
tx_div::load('tx_t3users_models_feuser');
tx_div::load('tx_t3sportsbet_util_ScopeController');



/**
 * Der View zeigt Tiprunden an und speichert Veränderungen.
 */
class tx_t3sportsbet_actions_BetList extends tx_rnbase_action_BaseIOC {
	
	/**
	 * 
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 * @return string error msg or null
	 */
	function handleRequest(&$parameters,&$configurations, &$viewData){
		$betgameUid = intval($configurations->get('betsetlist.betgame'));
		if(!$betgameUid) tx_rnbase_util_Misc::mayday($configurations->getLL('error_nobetgame_defined'));
		
		$feuser = tx_t3users_models_feuser::getCurrent();
		if(!$feuser)
			return 'Please login!';
		$betgame = tx_t3sportsbet_models_betgame::getInstance($betgameUid);
		// Wir zeigen entweder die offenen oder die schon fertigen Tipps
		// Dies wird per Config festgelegt
		$options['betgame'] = $betgame;
		$scopeArr = tx_t3sportsbet_util_ScopeController::handleCurrentScope($parameters,$configurations, $options);
		$rounds = $this->getRoundsFromScope($scopeArr['BETSET_UIDS']);
		
		$this->handleSubmit($feuser);
		
		// Über die viewdata können wir Daten in den View transferieren
		$viewData->offsetSet('betgame', $betgame);
		$viewData->offsetSet('rounds', $rounds);
		$viewData->offsetSet('feuser', $feuser);
		
		// Wenn wir hier direkt etwas zurückgeben, wird der View nicht
		// aufgerufen. Eher für Abbruch im Fehlerfall gedacht.
	  return null;
	}

	function getRoundsFromScope($uids) {
		$uids = t3lib_div::intExplode(',', $uids);
		$rounds = array();
		for($i=0, $cnt=count($uids); $i <$cnt; $i++) {
			$rounds[] = tx_t3sportsbet_models_betset::getInstance($uids[$i]);
		}
		return $rounds;
	}
	function handleSubmit($feuser) {
		$srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
		$data = t3lib_div::_GP('betset');
		if(!is_array($data)) return;
		tx_div::load('tx_cfcleaguefe_models_match');
		// Die Tips speichern
		foreach($data As $betsetUid => $matchArr) {
			$betset = tx_t3sportsbet_models_betset::getInstance($betsetUid);
			if(!$betset->isValid() || $betset->isFinished()) continue;
			foreach($matchArr As $matchUid => $betArr) {
				$match = tx_cfcleaguefe_models_match::getInstance($matchUid);
				list($betUid, $betData) = each($betArr);
				$srv->saveOrUpdateBet($betset, $match, $feuser, $betUid, $betData);
			}
		}
	}
  function getTemplateName() { return 'betlist';}
	function getViewClassName() { return 'tx_t3sportsbet_views_BetList';}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/actions/class.tx_t3sportsbet_actions_BetList.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/actions/class.tx_t3sportsbet_actions_BetList.php']);
}

?>