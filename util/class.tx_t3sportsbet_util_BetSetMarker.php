<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Rene Nitzsche (rene@system25.de)
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

tx_div::load('tx_rnbase_util_BaseMarker');

/**
 * Diese Klasse ist für die Erstellung von Markerarrays der Tipprunden verantwortlich
 */
class tx_t3sportsbet_util_BetSetMarker extends tx_rnbase_util_BaseMarker {
	function tx_t3sportsbet_util_BetSetMarker($options = array()) {
		$this->options = $options;
	}

	/**
	 * @param string $template das HTML-Template
	 * @param tx_t3sportsbet_models_betset $betset die Tipprunde
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $confId Pfad der TS-Config des Vereins, z.B. 'listView.round.'
	 * @param string $marker Name des Markers für die Tipprunde, z.B. ROUND
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, &$betset, &$formatter, $confId, $marker = 'BETSET') {
		if(!is_object($betset)) {
			$betset = self::getEmptyInstance('tx_t3sportsbet_models_betset');
		}
		$currItem = isset($this->options['currItem']) ? $this->options['currItem'] : false;
		$betset->record['isCurrent'] = $currItem && $currItem->uid == $betset->uid;
		// Die Spiele einbinden.
		if($this->containsMarker($template, $marker.'_MATCHS'))
			$template = $this->_addMatches($template, $betset, $formatter, $confId.'match.', $marker.'_MATCH');
		
		$markerArray = $formatter->getItemMarkerArrayWrapped($betset->record, $confId , 0, $marker.'_',$betset->getColumnNames());
		$subpartArray = array();
		$wrappedSubpartArray = array();
		$this->prepareLinks($betset, $marker, $markerArray, $subpartArray, $wrappedSubpartArray, $confId, $formatter);
		$template = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

		$markerArray = array();
		$subpartArray = array();
		$wrappedSubpartArray = array();
    
		$params['confid'] = $confId;
		$params['marker'] = $marker;
		$params['betset'] = $betset;
		self::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
		$out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray,$wrappedSubpartArray);
		
		return $out;
	}
	/**
	 * Add matches of betset
	 *
	 * @param tx_t3sportsbet_models_betset $betset
	 * @param string $template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * @return string
	 */
	private function _addMatches($template, &$betset, &$formatter, $confId, $marker) {
		$srv = tx_cfcleaguefe_util_ServiceRegistry::getMatchService();
		$fields['BETSETMM.UID_LOCAL'][OP_EQ_INT] = $betset->getUid();
		$options = array();
		tx_rnbase_util_SearchBase::setConfigFields($fields, $formatter->configurations, $confId.'fields.');
		tx_rnbase_util_SearchBase::setConfigOptions($options, $formatter->configurations, $confId.'options.');
		$children = $srv->search($fields, $options);

		$markerParams = $this->options;
		$markerParams['betset'] = $betset;
		
		$builderClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListBuilder');
		$listBuilder = new $builderClass();
		$out = $listBuilder->render($children,
						tx_div::makeInstance('tx_lib_spl_arrayObject'), $template, 'tx_t3sportsbet_util_MatchMarker',
						$confId, $marker, $formatter, $markerParams);
		return $out;
	}

	/**
	 * Links vorbereiten
	 *
	 * @param tx_t3sports_models_betset $betset
	 * @param string $marker
	 * @param array $markerArray
	 * @param array $wrappedSubpartArray
	 * @param string $confId
	 * @param tx_rnbase_util_FormatUtil $formatter
	 */
	private function prepareLinks(&$betset, $marker, &$markerArray, &$subpartArray, &$wrappedSubpartArray, $confId, &$formatter) {
		$currItem = isset($this->options['currItem']) ? $this->options['currItem'] : false;
		// Link bauen, wenn: kein $currItem oder $currItem != $betset
		$linkId = 'scope';
		if(!intval($betset->record['isCurrent']))
			$this->initLink($markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId, $linkId, $marker, array('betset' => $betset->uid));
		else {
			$linkMarker = $marker . '_' . strtoupper($linkId).'LINK';
			$remove = intval($formatter->configurations->get($confId.'links.'.$linkId.'.removeIfDisabled')); 
			$this->disableLink($markerArray, $subpartArray, $wrappedSubpartArray, $linkMarker, $remove > 0);
		}
	}

	/**
	 * Initialisiert die Labels für die Club-Klasse
	 *
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param array $defaultMarkerArr
	 */
	public function initLabelMarkers(&$formatter, $confId, $defaultMarkerArr = 0, $marker = 'ROUND') {
		return $this->prepareLabelMarkers('tx_t3sportsbet_models_betset', $formatter, $confId, $defaultMarkerArr, $marker);
	}
	/**
	 * Returns a List-Marker instance
	 *
	 * @return tx_rnbase_util_ListMarker
	 */
	private function _getListMarker() {
		$markerClass = tx_div::makeInstanceClassName('tx_rnbase_util_ListMarker');
		return new $markerClass;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_BetSetMarker.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_BetSetMarker.php']);
}
?>