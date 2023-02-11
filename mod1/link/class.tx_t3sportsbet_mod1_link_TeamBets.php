<?php

use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Module\Linker\LinkerInterface;

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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

class tx_t3sportsbet_mod1_link_TeamBets implements LinkerInterface
{
    /**
     * Buttons for team bets.
     *
     * @param tx_t3sportsbet_models_teamquestion $item
     * @param tx_rnbase_util_FormTool $formTool
     * @param int $currentPid
     * @param array $options
     *
     * @return string
     */
    public function makeLink($item, ToolBox $formTool, $currentPid, $options)
    {
        // , $GLOBALS['LANG']->getLL('label_edit')
        $out = $formTool->createEditButton('tx_t3sportsbet_teamquestions', $item->getUid());
        $cnt = tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->getBetCount($item);
        if ($cnt) {
            $out .= '<br />'.$formTool->createSubmit('showTeamBets['.$item->getUid().']', $GLOBALS['LANG']->getLL('label_showbets').' ('.$cnt.')');

            $betset = $item->getBetSet();
            if (!$betset->isFinished()) {
                $out .= '<br />'.$formTool->createSubmit('resetTeamBets['.$item->getUid().']', $GLOBALS['LANG']->getLL('label_resetbets'), $GLOBALS['LANG']->getLL('msg_resetbets'));
            }
        }

        return $out;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/link/class.tx_t3sportsbet_mod1_link_TeamBets.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/link/class.tx_t3sportsbet_mod1_link_TeamBets.php'];
}
