<?php

namespace Sys25\T3sportsbet\Module\Link;

use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Module\Linker\LinkerInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2021 Rene Nitzsche (rene@system25.de)
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

class MatchBetsLink implements LinkerInterface
{
    /**
     * Buttons for match bets.
     *
     * @param tx_cfcleaguefe_models_match $match
     * @param tx_rnbase_util_FormTool $formTool
     * @param int $currentPid
     * @param Tx_Rnbase_Domain_Model_Data|array $options
     *
     * @return string
     */
    public function makeLink($match, ToolBox $formTool, $currentPid, $options)
    {
        $out = $formTool->createEditLink('tx_cfcleague_games', $match->getUid(), $GLOBALS['LANG']->getLL('label_edit'));
        $options = is_object($options) ? $options->getProperty() : $options;
        if (isset($options['module'])) {
            $out .= '<br />';
            $betset = $options['currentRound'];
            $cnt = $betset->getBetCount($match);
            if ($cnt) {
                $out .= $formTool->createSubmit('showBets['.$match->getUid().']', $GLOBALS['LANG']->getLL('label_showbets').' ('.$cnt.')');
            }
            // $out .= $GLOBALS['LANG']->getLL('label_numberOfBets').': ' . $cnt;
            // Wenn das Spiel ausgewertet wurde und die Tiprunde noch offen ist
            if (!$betset->isFinished() && ('FINISHED' == $betset->getMatchState($match))) {
                $out .= '<br />'.$formTool->createSubmit('resetBets['.$match->getUid().']', $GLOBALS['LANG']->getLL('label_resetbets'), $GLOBALS['LANG']->getLL('msg_resetbets'));
            }
        }

        return $out;
    }
}
