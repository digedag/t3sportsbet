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
tx_div::load('tx_rnbase_util_Misc');


/**
 * Keine echte Registry, aber eine zentrale Klasse für den Zugriff auf verschiedene
 * Services
 */
class tx_t3sportsbet_util_serviceRegistry {

	/**
	 * Returns the bet service
	 * @return tx_t3sportsbet_services_betcalculator
	 */
	static function getCalculatorService() {
		return self::getService('t3sportsbet', 'calculator');
	}

	/**
	 * Returns the bet service
	 * @return tx_t3sportsbet_services_bet
	 */
	static function getBetService() {
		return self::getService('t3sportsbet', 'bet');
	}
	/**
	 * Returns the available data providers for matches
	 * @return array
	 */
	function lookupDataProvider($config) {
		$services = tx_rnbase_util_Misc::lookupServices('t3sportsbet_dataprovider');
		foreach ($services As $subtype => $info) {
			$title = $info['title'];
			if(substr($title, 0, 4) === 'LLL:') {
				$title = $GLOBALS['LANG']->sL($title);
			}
			$config['items'][] = array($title, $subtype);
		}
		return $config;
	}

	static function getService($type, $subType) {
    $srv = t3lib_div::makeInstanceService($type, $subType);
    if(!is_object($srv)) {
    	tx_div::load('tx_rnbase_util_Misc');
      return tx_rnbase_util_Misc::mayday('Service ' . $type . ' - ' . $subType . ' not found!');;
    }
    return $srv;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_serviceRegistry.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_serviceRegistry.php']);
}
?>