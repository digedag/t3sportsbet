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
 * Make additional join for match search to table tx_t3sportsbet_betsets_mm.
 *
 * @author Rene Nitzsche
 */
class SearchHook
{
    public function getTableMappingMatch($params, $parent)
    {
        $params['tableMapping']['BETSETMM'] = 'tx_t3sportsbet_betsets_mm';
    }

    public function getJoinsMatch($params, $parent)
    {
        if (isset($params['tableAliases']['BETSETMM'])) {
            $params['join'][] = new Join('MATCH', 'tx_t3sportsbet_betsets_mm', 'MATCH.uid = BETSETMM.uid_foreign', 'BETSETMM');
        }
    }

    public function getTableMappingTeam($params, $parent)
    {
        $params['tableMapping']['TEAMQUESTIONMM'] = 'tx_t3sportsbet_teamquestions_mm';
    }

    public function getJoinsTeam($params, $parent)
    {
        if (isset($params['tableAliases']['TEAMQUESTIONMM'])) {
            $params['join'][] = new Join('TEAM', 'tx_t3sportsbet_teamquestions_mm', 'TEAM.uid = TEAMQUESTIONMM.uid_foreign', 'TEAMQUESTIONMM');
        }
    }
}
