<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2016 Rene Nitzsche (rene@system25.de)
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


/**
 * Diese Klasse ist für die Darstellung von Tips im Backend verantwortlich
 */
class tx_t3sportsbet_util_BetDecorator {

	public function setFormTool($formTool) {
		$this->formTool = $formTool;
	}
	public function format($value, $colName) {
		$ret = $value;
		if($colName == 'tstamp') {
			$ret = date('H:i d.m.Y', $value);
		}
		elseif($colName == 't3matchresult') {
			if(is_object($value)) {
				tx_rnbase::load('tx_cfcleaguefe_models_match');
				$match = tx_cfcleaguefe_models_match::getMatchInstance($value->record['t3match']);
				$ret = $match->getResult();
			}
		}
		elseif($colName == 't3match') {
			tx_rnbase::load('tx_cfcleaguefe_models_match');
			$match = tx_cfcleaguefe_models_match::getMatchInstance($value);
			$ret = $match->getHomeNameShort() . ' - ' . $match->getGuestNameShort();
			$ret .= $this->formTool->createEditLink('tx_cfcleague_games', $match->uid, '');
		}
		elseif($colName == 'fe_user') {
			tx_rnbase::load('tx_t3users_models_feuser');
			$feuser = tx_t3users_models_feuser::getInstance($value);
			$ret = $feuser->getProperty('username');
			$ret .= $this->formTool->createEditLink('fe_users', $feuser->getUid(), '');
		}
		if($colName == 'uid') {
			$ret = $value . ' ' . $this->formTool->createEditLink('tx_t3sportsbet_bets', $value, '');
		}
		if($colName == 'bet') {
			$ret = (is_object($value)) ? $value->record['goals_home'] . ':' . $value->record['goals_guest'] : '-';
		}
		return $ret;
	}
}

