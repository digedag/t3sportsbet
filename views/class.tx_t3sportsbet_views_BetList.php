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


tx_div::load('tx_rnbase_view_Base');
tx_div::load('tx_rnbase_util_BaseMarker');



/**
 * Viewklasse für die Darstellung von Tipplisten
 */
class tx_t3sportsbet_views_BetList extends tx_rnbase_view_Base {

	function createOutput($template, &$viewData, &$configurations, &$formatter) {
		// Wir holen die Daten von der Action ab
		$betgame =& $viewData->offsetGet('betgame');
		$feuser =& $viewData->offsetGet('feuser');

		$params['confid'] = 'betlist.';
		$params['betgame'] = $betgame;
		$params['feuser'] = $feuser;
		$subpartArray = array();
    $subpartArray['###BETSET_SELECTIONS###'] = '';
		$wrappedSubpartArray = array();

		// Wenn Selectbox für Tiprunde gezeigt werden soll, dann Abschnitt erstellen
		$selectItems = $viewData->offsetGet('betset_select');
		$selectItems = is_array($selectItems) ? $selectItems : array();
		$template = $this->addScope($template, $viewData, $selectItems, 'betlist.betset.', 'BETSET', $formatter);
		
		$betsets =& $viewData->offsetGet('rounds');
		if(count($betsets)) {
			$builderClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListBuilder');
			$listBuilder = new $builderClass();
			$template = $listBuilder->render($betsets,
					$viewData, $template, 'tx_t3sportsbet_util_BetSetMarker',
					'betlist.betset.', 'BETSET', $formatter, $params);
			$markerArray['###ACTION_URI###'] = $this->createPageUri($configurations);
			$matchState = $viewData->offsetGet('MATCH_STATE');
			if($matchState == 'OPEN')
				$wrappedSubpartArray['###SAVEBUTTON###'] = array('','');
			else
				$subpartArray['###SAVEBUTTON###'] = '';
		}
		else {
			$subpartArray['###BETSETS###'] = $configurations->getLL('msg_no_betsets_found');
//			$out = $formatter->cObj->substituteMarkerArrayCached($template, array(), $subpartArray);
		}
		tx_rnbase_util_BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
		$out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray,$wrappedSubpartArray);
		return $out;
	}
	function createPageUri(&$configurations, $params = array(), $nocache = true) {
		$link = $configurations->createLink();
		$link->destination($targetPid ? $targetPid : $GLOBALS['TSFE']->id);
		if(count($params))
			$link->parameters($params);
		if($nocache)
			$link->nocache();
		return $link->makeUrl(false);
	}

	private function addScope($template, &$viewData, &$itemsArr, $confId, $markerName, &$formatter) {
		if(count($itemsArr)) {
			$betsets = $itemsArr[0];
			$currItem = $betsets[$itemsArr[1]];
			// Die Betsets liegen in einem Hash, sie müssen aber in ein einfaches Array
			$betsets = array_values($betsets);
		}
		$builderClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListBuilder');
		$listBuilder = new $builderClass();
		$template = $listBuilder->render($betsets,
				$viewData, $template, 'tx_t3sportsbet_util_BetSetMarker',
				$confId.'selection.', $markerName . '_SELECTION', $formatter, array('currItem' => $currItem));
		return $template;
	}

	/**
	 * Subpart der im HTML-Template geladen werden soll. Dieser wird der Methode
	 * createOutput automatisch als $template übergeben. 
	 *
	 * @return string
	 */
	function getMainSubpart() {
		return '###BETLIST###';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/views/class.tx_t3sportsbet_views_BetList.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/views/class.tx_t3sportsbet_views_BetList.php']);
}
?>