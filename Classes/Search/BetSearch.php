<?php

namespace Sys25\T3sportsbet\Search;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2023 Rene Nitzsche
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

use Sys25\RnBase\Database\Query\Join;
use Sys25\RnBase\Search\SearchBase;
use Sys25\T3sportsbet\Model\Bet;

/**
 * Class to search betsets from database.
 *
 * @author Rene Nitzsche
 */
class BetSearch extends SearchBase
{
    protected function getTableMappings()
    {
        $tableMapping = [];
        $tableMapping['BET'] = 'tx_t3sportsbet_bets';
        $tableMapping['BETSET'] = 'tx_t3sportsbet_betsets';
        $tableMapping['BETGAME'] = 'tx_t3sportsbet_betgames';
        $tableMapping['MATCH'] = 'tx_cfcleague_games';

        return $tableMapping;
    }

    protected function getBaseTable()
    {
        return 'tx_t3sportsbet_bets';
    }

    protected function getBaseTableAlias()
    {
        return 'BET';
    }

    public function getWrapperClass()
    {
        return Bet::class;
    }

    protected function getJoins($tableAliases)
    {
        $join = [];
        if (isset($tableAliases['BETSET']) || isset($tableAliases['BETGAME'])) {
            $join[] = new Join('BET', 'tx_t3sportsbet_betsets', 'BET.betset = BETSET.uid', 'BETSET');
        }
        if (isset($tableAliases['BETGAME'])) {
            $join[] = new Join('BETSET', 'tx_t3sportsbet_betgames', 'BETGAME.uid = BETSET.betgame', 'BETGAME');
        }
        if (isset($tableAliases['MATCH'])) {
            $join[] = new Join('BET', 'tx_cfcleague_games', 'BET.t3match = MATCH.uid', 'MATCH');
        }

        return $join;
    }
}
