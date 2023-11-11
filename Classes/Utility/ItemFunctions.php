<?php

namespace Sys25\T3sportsbet\Utility;

use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\T3General;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Model\TeamQuestion;
use tx_rnbase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2023 Rene Nitzsche (rene@system25.de)
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
 * Diese Klasse ist für die Erstellung von Auswahllisten in TCEforms verantwortlich.
 */
class ItemFunctions
{
    /**
     * Used in flexform to lookup betset for a betgame in highscore list.
     *
     * @param array $config
     */
    public function getBetSet4BetGame($config)
    {
        $rowName = isset($config['flexParentDatabaseRow']) ? 'flexParentDatabaseRow' : 'row';
        if (!$config[$rowName]['pi_flexform']) {
            return;
        }
        $flex = is_array($config[$rowName]['pi_flexform']) ? $config[$rowName]['pi_flexform'] :
            T3General::xml2array($config[$rowName]['pi_flexform']);

        $betgameUid = $flex['data']['sDEF']['lDEF']['scope.betgame']['vDEF'] ?? false;
        if (!$betgameUid) {
            return;
        }

        $options = ['where' => 'tx_t3sportsbet_betsets.betgame = '.$betgameUid];
        Misc::prepareTSFE();
        $records = Connection::getInstance()->doSelect(
            'round_name, uid',
            'tx_t3sportsbet_betsets',
            $options
        );
        foreach ($records as $record) {
            $config['items'][] = array_values($record);
        }
    }

    /**
     * Used in TCA.
     * Return all teams of a betsets betgame.
     *
     * @param array $PA
     * @param \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems $fobj
     */
    public function getTeams4TeamBet($PA, $fobj)
    {
        if ($PA['row']['betset']) {
            if (!$PA['row']['uid']) {
                return;
            }
            $betset = $this->loadBetset($PA['row']['betset']);
            $teamQuestion = tx_rnbase::makeInstance(TeamQuestion::class, $PA['row']['uid']);
            $teams = ServiceRegistry::getTeamBetService()->getTeams4TeamQuestion($teamQuestion);
            foreach ($teams as $team) {
                $PA['items'][] = [
                    $team->getName(),
                    $team->getUid(),
                ];
            }
        }
    }

    /**
     * Load a betset from database.
     *
     * @param int $uid
     *
     * @return BetSet
     */
    private function loadBetset($fieldData)
    {
        if (is_array($fieldData)) {
            if (empty($fieldData)) {
                return false;
            }
            $row = $fieldData[0]['row'];

            return tx_rnbase::makeInstance(BetSet::class, $row);
        } else {
            $arr = Strings::trimExplode('|', $fieldData);
            $arr = Strings::trimExplode('_', $arr[0]);
            $uid = (int) $arr[count($arr) - 1];

            return $uid ? tx_rnbase::makeInstance(BetSet::class, $uid) : false;
        }
    }
}
