<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Rene Nitzsche
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
 * Class to search team questions from database
 *
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_search_TeamQuestion extends tx_rnbase_util_SearchBase
{

    protected function getTableMappings()
    {
        $tableMapping = [];
        $tableMapping['TEAMQUESTION'] = 'tx_t3sportsbet_teamquestions';
        $tableMapping['BETSET'] = 'tx_t3sportsbet_betsets';
        return $tableMapping;
    }

    protected function useAlias()
    {
        return true;
    }

    protected function getBaseTable()
    {
        return 'tx_t3sportsbet_teamquestions';
    }

    protected function getBaseTableAlias()
    {
        return 'TEAMQUESTION';
    }

    function getWrapperClass()
    {
        return 'tx_t3sportsbet_models_teamquestion';
    }

    protected function getJoins($tableAliases)
    {
        $join = '';
        if (isset($tableAliases['BETSET'])) {
            $join .= ' JOIN tx_t3sportsbet_betsets AS BETSET ON BETSET.uid = TEAMQUESTION.betset ';
        }
        return $join;
    }
}

