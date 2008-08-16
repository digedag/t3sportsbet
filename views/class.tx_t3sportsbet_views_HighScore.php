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
tx_div::load('tx_rnbase_util_ListBuilder');


/**
 * Viewklasse für die Darstellung der Bestenliste
 */
class tx_t3sportsbet_views_HighScore extends tx_rnbase_view_Base {

	function createOutput($template, &$viewData, &$configurations, &$formatter) {
		// Wir holen die Daten von der Action ab
		$betgame =& $viewData->offsetGet('betgame');
		$userPoints =& $viewData->offsetGet('userPoints');
		$currUserPoints =& $viewData->offsetGet('currUserPoints');
		$userSize = $viewData->offsetGet('userSize');

		// Wenn Selectbox für Tiprunde gezeigt werden soll, dann Abschnitt erstellen
		$selectItems = $viewData->offsetGet('betset_select');
		$selectItems = is_array($selectItems) ? $selectItems : array();
		$template = $this->addScope($template, $viewData, $selectItems, 'highscore.betset.', 'BETSET', $formatter);
		
		// Wir haben jetzt erstmal nur die UIDs und die Punktezahl. Die Nutzerdaten müssen erst geladen werden
		$users = $this->getUsers($userPoints, $userSize);
		$builderClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListBuilder');
		$listBuilder = new $builderClass();
		$template = $listBuilder->render($users,
					$viewData, $template, 'tx_t3users_util_FeUserMarker',
					'highscore.feuser.', 'FEUSER', $formatter);

		// Anzeige des aktuellen Users
		$subpartArray['###CURRUSER###'] = $this->_addCurrUser($currUserPoints,
						$formatter->cObj->getSubpart($template,'###CURRUSER###'),
						$formatter, 'currUser.', 'CURRUSER');
		$template = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray,$wrappedSubpartArray);

		$params['confid'] = 'highscore.';
		$params['betgame'] = $betgame;
		$markerArray = array();	$subpartArray = array();	$wrappedSubpartArray = array();
		
		tx_rnbase_util_BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
		$out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray,$wrappedSubpartArray);
		return $out;
	}
	function _addCurrUser($currUserPoints, $template, &$formatter, $confId, $marker) {
		$feuser = tx_t3users_models_feuser::getInstance($currUserPoints['uid']);
		if(!$feuser->isValid()) return '';
		$this->setAddUserData($feuser, $currUserPoints);

		$markerArray = $formatter->getItemMarkerArrayWrapped($feuser->record, $confId , 0, $marker.'_',$feuser->getColumnNames());
		$template = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray,$wrappedSubpartArray);
		return $template;
	}
	/**
	 * Return feuser objects for point list.
	 *
	 * @param array $userPoints
	 * @return array
	 */
	function getUsers($userPoints, $userSize) {
		$users = array();
		for($i=0, $cnt=count($userPoints); $i < $cnt; $i++) {
			$feuser = tx_t3users_models_feuser::getInstance($userPoints[$i]['uid']);
			$this->setAddUserData($feuser, $userPoints[$i]);
//			$feuser->record['betpoints'] = $userPoints[$i]['betpoints'];
//			$feuser->record['betrank'] = $userPoints[$i]['rank'];
//			$feuser->record['betmark'] = $userPoints[$i]['mark'];
			$users[] = $feuser;
		}
		return $users;
	}
	function setAddUserData(&$feuser, $data) {
		$feuser->record['betpoints'] = $data['betpoints'];
		$feuser->record['betrank'] = $data['rank'];
		$feuser->record['betmark'] = $data['mark'];
		$feuser->record['betcount'] = $data['betcount'];
		$feuser->record['avgpoints'] = $data['avgpoints'];
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
		return '###HIGHSCORE###';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/views/class.tx_t3sportsbet_views_HighScore.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/views/class.tx_t3sportsbet_views_HighScore.php']);
}
?>