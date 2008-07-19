<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');

/**
 * Make additional join for feuser search to table tx_t3sportsbet_bets
 * 
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_hooks_searchFeuser {

	function getTableMapping($params, $parent) {
		$params['tableMapping']['BET'] = 'tx_t3sportsbet_bets';
	}
	function getJoins($params, $parent) {
		if(isset($params['tableAliases']['BET'])) {
			$params['join'] .= ' INNER JOIN tx_t3sportsbet_bets ON fe_users.uid = tx_t3sportsbet_bets.fe_user ';
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/hooks/class.tx_t3sportsbet_hooks_searchFeuser.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/hooks/class.tx_t3sportsbet_hooks_searchFeuser.php']);
}

?>