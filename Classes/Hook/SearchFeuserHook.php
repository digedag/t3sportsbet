<?php

namespace Sys25\T3sportsbet\Hook;

use Sys25\RnBase\Database\Query\Join;

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

/**
 * Make additional join for feuser search to table tx_t3sportsbet_bets.
 *
 * @author Rene Nitzsche
 */
class SearchFeuserHook
{
    public function getTableMapping($params, $parent)
    {
        $params['tableMapping']['BET'] = 'tx_t3sportsbet_bets';
        $params['tableMapping']['BETSETRESULT'] = 'tx_t3sportsbet_betsetresults';
    }

    public function getJoins($params, $parent)
    {
        if (isset($params['tableAliases']['BET'])) {
            $params['join'][] = new Join('FEUSER', 'tx_t3sportsbet_bets', 'FEUSER.uid = BET.fe_user', 'BET');
        }
        if (isset($params['tableAliases']['BETSETRESULT'])) {
            $params['join'][] = new Join('FEUSER', 'tx_t3sportsbet_betsetresults', 'FEUSER.uid = BETSETRESULT.feuser', 'BETSETRESULT');
        }
    }
}
