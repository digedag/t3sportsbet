<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2010 Rene Nitzsche (rene@system25.de)
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
 * Diese Klasse ist für die Erstellung von Auswahllisten in TCEforms verantwortlich
 */
class tx_t3sportsbet_util_ItemFunctions {

	/**
	 * Used in flexform to lookup betset for a betgame in highscore list
	 *
	 * @param array $config
	 */
	function getBetSet4BetGame($config) {
		if(!$config['row']['pi_flexform']) return;
		$flex = t3lib_div::xml2array($config['row']['pi_flexform']);
		$betgameUid = $flex['data']['sDEF']['lDEF']['scope.betgame']['vDEF'];
		if(!$betgameUid) return;

		$options['where'] = 'tx_t3sportsbet_betsets.betgame = ' . $betgameUid;
		tx_rnbase::load('tx_rnbase_util_Misc');
		tx_rnbase_util_Misc::prepareTSFE();
		$records = tx_rnbase_util_DB::doSelect('round_name, uid', 'tx_t3sportsbet_betsets', $options, 0);
		foreach($records As $record) {
			$config['items'][] = array_values($record);
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_ItemFunctions.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_ItemFunctions.php']);
}
?>