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

use Sys25\RnBase\Search\SearchBase;
use Sys25\T3sportsbet\Model\Bet;

/**
 * Class to search betsets from database.
 *
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_search_Bet extends SearchBase
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

    public function getWrapperClass()
    {
        return Bet::class;
    }

    protected function getJoins($tableAliases)
    {
        $join = '';
        if (isset($tableAliases['BETSET']) || isset($tableAliases['BETGAME'])) {
            $join .= ' JOIN tx_t3sportsbet_betsets ON tx_t3sportsbet_bets.betset = tx_t3sportsbet_betsets.uid ';
        }
        if (isset($tableAliases['BETGAME'])) {
            $join .= ' JOIN tx_t3sportsbet_betgames ON tx_t3sportsbet_betsets.betgame = tx_t3sportsbet_betgames.uid ';
        }
        if (isset($tableAliases['MATCH'])) {
            $join .= ' JOIN tx_cfcleague_games ON tx_t3sportsbet_bets.t3match = tx_cfcleague_games.uid ';
        }

        return $join;
    }
}
