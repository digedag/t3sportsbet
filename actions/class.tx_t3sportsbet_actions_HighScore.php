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

tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3sportsbet_models_betgame');
tx_rnbase::load('tx_t3users_models_feuser');
tx_rnbase::load('tx_t3users_util_ServiceRegistry');
tx_rnbase::load('tx_t3sportsbet_util_ScopeController');



/**
 * Der View zeigt die Bestenliste an
 */
class tx_t3sportsbet_actions_HighScore extends tx_rnbase_action_BaseIOC {
	
	/**
	 * 
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 * @return string error msg or null
	 */
	function handleRequest(&$parameters,&$configurations, &$viewData){
//		$betgameUid = intval($configurations->get('highscore.betgame'));
//		if(!$betgameUid) tx_rnbase_util_Misc::mayday($configurations->getLL('error_nobetgame_defined'));

		// Mit den Betsets kann man Zwischenauswertungen machen
//		$betgame = tx_t3sportsbet_models_betgame::getInstance($betgameUid);
//		$options['betgame'] = $betgame;
		$scopeArr = tx_t3sportsbet_util_ScopeController::handleCurrentScope($parameters,$configurations, $options);
		$betgames = tx_t3sportsbet_util_ScopeController::getBetgamesFromScope($scopeArr['BETGAME_UIDS']);
		$betgameUids = $scopeArr['BETGAME_UIDS'];
		$betsetUids = $scopeArr['BETSET_UIDS'];
		// Um etwas zu zeigen, benötigen wir Betset-Ids
		if(!$betsetUids) {
			return $configurations->getLL('error_nobetsets_defined');
		}
		// Liste von Nutzern
		// Sortiert nach den Punkten in den Tips
		// 1. Abfrage: Anzahl der Nutzer mit Tips
		$betSrv = tx_t3sportsbet_util_ServiceRegistry::getBetService();
//		$listSize = $betSrv->getResultSize($betsetUids);
		// Die gesamten Daten holen
		$feuser = tx_t3users_models_feuser::getCurrent();
		$userUids = ($feuser) ? $feuser->uid : '';
		$results = $betSrv->getResults($betsetUids, $userUids);
		$listSize = count($results[0]);

		$pageBrowser = tx_rnbase::makeInstance('tx_rnbase_util_PageBrowser', 'bethighscores'.$configurations->getCObj()->data['uid']);
		$pageSize = $this->getPageSize($parameters, $configurations);
		$pageBrowser->setState($parameters, $listSize, $pageSize);
		$limit = $pageBrowser->getState();
		// Aus der Gesamtliste den gesuchten Abschnitt herausschneiden
		$userPoints = array_slice($results[0], $limit['offset'], $limit['limit']);
		$currUserPoints = ($feuser) ? $results[0][$results[1][$feuser->uid]] : array();
		
		$viewData->offsetSet('betgame', $betgames[0]);
		$viewData->offsetSet('userPoints', $userPoints);
		$viewData->offsetSet('currUserPoints', $currUserPoints);
		$viewData->offsetSet('userSize', $pageBrowser->getListSize());
		$viewData->offsetSet('pagebrowser', $pageBrowser);
		
	  return null;
	}
	/**
	 * Liefert die Anzahl der Ergebnisse pro Seite
	 *
	 * @param array $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @return int
	 */
	protected function getPageSize(&$parameters, &$configurations) {
		return intval($configurations->get('highscore.feuser.pagebrowser.limit'));
	}

  function getTemplateName() { return 'highscore';}
	function getViewClassName() { return 'tx_t3sportsbet_views_HighScore';}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/actions/class.tx_t3sportsbet_actions_HighScore.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/actions/class.tx_t3sportsbet_actions_HighScore.php']);
}

?>