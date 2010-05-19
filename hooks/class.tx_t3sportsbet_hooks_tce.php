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

/**
 * Hook-Klasse
 */
class tx_t3sportsbet_hooks_tce {
	/**
	 * Dieser Hook wird vor der Darstellung eines TCE-Formulars aufgerufen.
	 * Werte aus der Datenbank können vor deren Darstellung manipuliert werden.
	 *
	 */
	function getMainFields_preProcess($table,&$row, $tceform) {
		if($table == 'tx_t3sportsbet_betsets') {
			$betgame = intval(t3lib_div::_GP('betgame'));
			if($betgame) $row['betgame'] = $betgame;
			$round = intval(t3lib_div::_GP('round'));
			if($round) $row['round'] = $round;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/hooks/class.tx_t3sportsbet_hooks_tce.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/hooks/class.tx_t3sportsbet_hooks_tce.php']);
}

?>