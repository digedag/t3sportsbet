<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2010 Rene Nitzsche
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
tx_rnbase::load('tx_rnbase_util_SearchBase');

/**
 * Class to search betset results from database
 *
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_search_BetSetResult extends tx_rnbase_util_SearchBase
{

    protected function getTableMappings()
    {
        $tableMapping = [];
        $tableMapping['BETSETRESULT'] = 'tx_t3sportsbet_betsetresults';
        $tableMapping['BETSET'] = 'tx_t3sportsbet_betsets';
        $tableMapping['BETGAME'] = 'tx_t3sportsbet_betgames';

        // Hook to append other tables
        tx_rnbase_util_Misc::callHook('t3sportsbet', 'search_BetSetResult_getTableMapping_hook', array(
            'tableMapping' => &$tableMapping
        ), $this);
        return $tableMapping;
    }

    protected function getBaseTable()
    {
        return 'tx_t3sportsbet_betsetresults';
    }

    function getWrapperClass()
    {
        return 'tx_t3sportsbet_models_betsetresult';
    }

    protected function useAlias()
    {
        return true;
    }

    protected function getBaseTableAlias()
    {
        return 'BETSETRESULT';
    }

    protected function getJoins($tableAliases)
    {
        $join = '';
        if (isset($tableAliases['BETSET']) || isset($tableAliases['BETGAME'])) {
            $join .= ' INNER JOIN tx_t3sportsbet_betsets AS BETSET ON BETSET.uid = BETSETRESULT.betset ';
        }
        if (isset($tableAliases['BETGAME'])) {
            $join .= ' JOIN tx_t3sportsbet_betgames AS BETGAME ON BETSET.betgame = BETGAME.uid ';
        }

        // Hook to append other tables
        tx_rnbase_util_Misc::callHook('t3sportsbet', 'search_BetSetResult_getJoins_hook', array(
            'join' => &$join,
            'tableAliases' => $tableAliases
        ), $this);
        return $join;
    }
}
