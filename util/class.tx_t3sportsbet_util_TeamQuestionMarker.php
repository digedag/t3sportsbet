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

tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * Handle team questions
 */
class tx_t3sportsbet_util_TeamQuestionMarker extends tx_rnbase_util_BaseMarker {
	function __construct($options = array()) {
		$this->options = $options;
	}

	/**
	 * @param string $template das HTML-Template
	 * @param tx_t3sportsbet_models_teamquestion $item
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $confId Pfad der TS-Config des Vereins, z.B. 'listView.round.'
	 * @param string $marker Name des Markers für die Tipprunde, z.B. ROUND
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, $item, $formatter, $confId, $marker = 'TEAMBET') {
		if(!is_object($item)) {
			// Ist kein Verein vorhanden wird ein leeres Objekt verwendet.
			$bet = self::getEmptyInstance('tx_t3sportsbet_models_teamquestion');
		}
		$this->prepare($item, $template, $marker);
		// Es wird das MarkerArray mit den Daten des Tips gefüllt.
		$markerArray = $formatter->getItemMarkerArrayWrapped($item->record, $confId , 0, $marker.'_',$item->getColumnNames());

		$template = $this->addTeamPart($template, $item, $formatter, $confId.'match.', $marker.'_MATCH');

		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		return $out;
	}
	/**
	 * Add team selection
	 *
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @param string $template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * @return string
	 */
	private function addTeamPart($template, $teamQuestion, $formatter, $confId, $marker) {
		// 
	}
	/**
	 * 
	 * @param tx_t3sportsbet_models_teamquestion $item
	 * @param string $template
	 * @param string $marker
	 */
	private function prepare($item, $template, $marker) {
		$item->record['openuntiltstamp'] = $item->getOpenUntilTstamp();
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_TeamQuestionMarker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_TeamQuestionMarker.php']);
}
?>