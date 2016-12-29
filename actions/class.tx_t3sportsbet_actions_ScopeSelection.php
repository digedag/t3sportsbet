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
tx_rnbase::load('tx_t3sportsbet_util_ScopeController');



/**
 * Der View zeigt Auswahlbox für Tiprunden an und speichert Veränderungen.
 */
class tx_t3sportsbet_actions_ScopeSelection extends tx_rnbase_action_BaseIOC {

	/**
	 *
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewData
	 * @return string error msg or null
	 */
	function handleRequest(&$parameters,&$configurations, &$viewData){
		// Wir zeigen entweder die offenen oder die schon fertigen Tipps
		// Dies wird per Config festgelegt
		$options['betgame'] = $betgame;
		$options['betsetkey'] = 'scope.';
		$scopeArr = tx_t3sportsbet_util_ScopeController::handleCurrentScope($parameters,$configurations, $options);
		$betgames = tx_t3sportsbet_util_ScopeController::getBetgamesFromScope($scopeArr['BETGAME_UIDS']);
		$rounds = tx_t3sportsbet_util_ScopeController::getRoundsFromScope($scopeArr['BETSET_UIDS']);

		// Über die viewdata können wir Daten in den View transferieren
		$viewData->offsetSet('betgame', $betgames[0]);
		$viewData->offsetSet('rounds', $rounds);
		$viewData->offsetSet('feuser', $feuser);

		// Wenn wir hier direkt etwas zurückgeben, wird der View nicht
		// aufgerufen. Eher für Abbruch im Fehlerfall gedacht.
	  return null;
	}

	function getTemplateName() { return 'scope';}
	function getViewClassName() { return 'tx_t3sportsbet_views_ScopeSelection';}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/actions/class.tx_t3sportsbet_actions_ScopeSelection.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/actions/class.tx_t3sportsbet_actions_ScopeSelection.php']);
}

?>