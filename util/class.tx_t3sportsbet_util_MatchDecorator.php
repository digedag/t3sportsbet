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
 * Diese Klasse ist für die Darstellung von Spielen im Backend verantwortlich
 */
class tx_t3sportsbet_util_MatchDecorator {

	public function format($value, $colName) {
		$ret = $value;
		if($colName == 'date') {
			$ret = date('H:i d.m.y', $value);
		}
		if($colName == 'competition') {
			$comp = tx_cfcleaguefe_models_competition::getInstance($value);
			if(!is_object($comp) || !$comp->isValid()) return '';
			$group = $comp->getGroup();
			if(!is_object($group) || !$group->isValid()) return '';
			$name = (array_key_exists('shortname', $group->record)) ? $group->record['shortname'] : '';
			$ret = strlen($name) ? $name : $group->getName();
		}
		return $ret;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_MatchDecorator.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_MatchDecorator.php']);
}
?>