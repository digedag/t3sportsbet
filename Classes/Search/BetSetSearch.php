<?php

namespace Sys25\T3sportsbet\Search;

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

use Sys25\RnBase\Search\SearchBase;
use Sys25\T3sportsbet\Model\BetSet;

define('MATCHSRV_FIELD_MATCH_COMPETITION', 'MATCH.COMPETITION');
define('MATCHSRV_FIELD_MATCH_ROUND', 'MATCH.ROUND');
define('MATCHSRV_FIELD_MATCH_DATE', 'MATCH.DATE');

/**
 * Class to search betsets from database.
 *
 * @author Rene Nitzsche
 */
class BetSetSearch extends SearchBase
{
    protected function getTableMappings()
    {
        $tableMapping = [];
        $tableMapping['BETSET'] = 'tx_t3sportsbet_betsets';
        $tableMapping['BETGAME'] = 'tx_t3sportsbet_betgames';
        $tableMapping['BETSETMM'] = 'tx_t3sportsbet_betsets_mm';
        $tableMapping['MATCH'] = 'tx_cfcleague_games';

        return $tableMapping;
    }

    protected function getBaseTable()
    {
        return 'tx_t3sportsbet_betsets';
    }

    public function getWrapperClass()
    {
        return BetSet::class;
    }

    protected function getJoins($tableAliases)
    {
        $join = '';
        if (isset($tableAliases['BETGAME'])) {
            $join .= ' JOIN tx_t3sportsbet_betgames ON tx_t3sportsbet_betsets.betgame = tx_t3sportsbet_betgames.uid ';
        }
        if (isset($tableAliases['BETSETMM']) || isset($tableAliases['MATCH'])) {
            $join .= ' INNER JOIN tx_t3sportsbet_betsets_mm ON tx_t3sportsbet_betsets.uid = tx_t3sportsbet_betsets_mm.uid_local ';
        }
        if (isset($tableAliases['MATCH'])) {
            $join .= ' INNER JOIN tx_cfcleague_games ON tx_cfcleague_games.uid = tx_t3sportsbet_betsets_mm.uid_foreign ';
        }

        return $join;
    }
}
