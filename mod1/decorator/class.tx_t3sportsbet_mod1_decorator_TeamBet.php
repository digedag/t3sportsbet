<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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
 * Show team bets in BE module
 */
class tx_t3sportsbet_mod1_decorator_TeamBet {
	function __construct($mod) {
		$this->mod = $mod;
	}

	/**
	 * Returns the module
	 * @return tx_rnbase_mod_IModule
	 */
	private function getModule() {
		return $this->mod;
	}
	/**
	 * 
	 * @param string $value
	 * @param string $colName
	 * @param array $record
	 * @param tx_t3sportsbet_models_teamquestion $item
	 */
	public function format($value, $colName, $record, $item) {
		$ret = $value;
		if($colName == 'uid') {
			$ret = $this->getModule()->getFormTool()->createEditLink('tx_t3sportsbet_teambets', $item->getUid(), '');
			$wrap = $item->record['hidden'] ? array('<strike>', '</strike>') : array('<strong>', '</strong>');
			$ret .= $wrap[0]. $value . $wrap[1].'<br />';
		}
		elseif($colName == 'tstamp') {
			$ret = date('H:i d.m.Y', $value);
		}
		elseif($colName == 'finished') {
			$ret = $GLOBALS['LANG']->getLL($value ? 'label_yes' : 'label_no' );
		}
		elseif($colName == 'team') {
			$team = tx_rnbase::makeInstance('tx_cfcleague_models_Team', $value);
			$ret = $team->getName();
		}
		elseif($colName == 'feuser') {
			tx_rnbase::load('tx_t3users_models_feuser');
			$feuser = tx_t3users_models_feuser::getInstance($value);
			$ret = $feuser->record['username'];
			$ret .= $this->getModule()->getFormTool()->createEditLink('fe_users', $feuser->uid, '');
		}
		return $ret;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/decorator/class.tx_t3sportsbet_mod1_decorator_TeamBet.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/decorator/class.tx_t3sportsbet_mod1_decorator_TeamBet.php']);
}
?>