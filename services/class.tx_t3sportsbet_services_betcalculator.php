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
require_once(PATH_t3lib.'class.t3lib_svbase.php');




/**
 * This service calculates the points for a bet
 * 
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_services_betcalculator extends t3lib_svbase  {

	/**
	 * Calculates the points for a bet
	 *
	 * @param tx_t3sportsbet_models_betgame $betGame
	 * @param tx_t3sportsbet_models_bet $bet
	 */
	public function calculatePoints($betgame, $bet) {
		$match = $bet->getMatch();
		// TODO: GreenTable kann noch nicht ermittelt werden...
		// 1. Schritt: Spielergebnis ermitteln
		$mpart = '';
		$mpart = ($match->isExtraTime() && $betgame->isDrawIfExtraTime()) ? 'et' : $mpart;
		$mpart = ($match->isPenalty() && $betgame->isDrawIfPenalty()) ? 'ap' : $mpart;
		$goalsHome = $match->getGoalsHome($mpart);
		$goalsGuest = $match->getGoalsGuest($mpart);
		
		$ret = 0;
		// Auswertung nach
		// Genauer Tip
		if($bet->getGoalsHome() == $goalsHome && $bet->getGoalsGuest() == $goalsGuest) {
			$ret = $betgame->getPointsAccurate();
		}
		elseif ($bet->getToto() == $match->getToto($mpart)) {
			// Tendency
			$ret = $betgame->getPointsTendency();
		}
		return $ret;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/services/class.tx_t3sportsbet_services_betcalculator.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/services/class.tx_t3sportsbet_services_betcalculator.php']);
}

?>