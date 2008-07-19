<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
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

tx_div::load('tx_rnbase_util_SearchBase');

define('MATCHSRV_FIELD_MATCH_COMPETITION', 'MATCH.COMPETITION');
define('MATCHSRV_FIELD_MATCH_ROUND', 'MATCH.ROUND');
define('MATCHSRV_FIELD_MATCH_DATE', 'MATCH.DATE');


/**
 * Class to search betsets from database
 * 
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_search_BetSet extends tx_rnbase_util_SearchBase {

	protected function getTableMappings() {
		$tableMapping['BETSET'] = 'tx_t3sportsbet_betsets';
		$tableMapping['BETGAME'] = 'tx_t3sportsbet_betgames';
		return $tableMapping;
	}

	protected function getBaseTable() {
		return 'tx_t3sportsbet_betsets';
	}
	function getWrapperClass() {
		return 'tx_t3sportsbet_models_betset';
	}

	protected function getJoins($tableAliases) {
		$join = '';
		if(isset($tableAliases['BETGAME'])) {
			$join .= ' JOIN tx_t3sportsbet_betgames ON tx_t3sportsbet_betsets.betgame = tx_t3sportsbet_betgames.uid ';
		}
		return $join;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/search/class.tx_t3sportsbet_search_BetSet.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/search/class.tx_t3sportsbet_search_BetSet.php']);
}

?>