<?php

namespace Sys25\T3sportsbet\Module\Controller\BetGame;

use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Module\IModFunc;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Utility\T3General;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Model\TeamQuestion;
use Sys25\T3sportsbet\Module\Link\TeamBetsLink;
use Sys25\T3sportsbet\Module\Lister\TeamBetLister;
use Sys25\T3sportsbet\Module\Lister\TeamQuestionLister;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use tx_rnbase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2023 Rene Nitzsche (rene@system25.de)
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
 * Manage TeamBets.
 */
class AddTeamBets
{
    /**
     * @var IModule
     */
    protected $module;

    /**
     * @var BetSet
     */
    protected $currentRound;

    /**
     * @param IModule $module
     * @param BetSet $currentRound
     */
    public function __construct($module, $currentRound)
    {
        $this->module = $module;
        $this->currentRound = $currentRound;
    }

    /**
     * @return string
     */
    public function handleRequest()
    {
        $out = '';
        $out .= $this->handleResetTeamBets($this->currentRound);
        $out .= $this->handleShowTeamBets($this->currentRound);

        return $out;
    }

    /**
     * Ausführung des Requests.
     *
     * @return string
     */
    public function show()
    {
        $options = [];
        $options['title'] = '###LABEL_BTN_NEWTEAMBET###';
        $options['params'] = '&defVals[tx_t3sportsbet_teamquestions][betset]=tx_t3sportsbet_betsets_'.$this->currentRound->getUid();
        $options['linker'][] = tx_rnbase::makeInstance(TeamBetsLink::class);

        $lister = tx_rnbase::makeInstance(TeamQuestionLister::class, $this->module, $options);
        $lister->setBetSetUid($this->currentRound->getUid());

        $list = $lister->getResultList();
        $out = $this->module->getDoc()->spacer(10);
        $out .= $list['pager']."\n<div style=\"clear:both;\"></div>\n".$list['table'];
        $out .= $this->getFormTool()->createNewButton('tx_t3sportsbet_teamquestions', $this->currentRound->getProperty('pid'), $options);
        $out .= $this->module->getDoc()->spacer(10);

        return $out;
    }

    /**
     * Show a list of bets for a team question.
     *
     * @param BetSet $currBetSet
     *
     * @return string
     */
    private function handleShowTeamBets(BetSet $currBetSet)
    {
        $teamQuestionUid = $this->module->getFormTool()->getStoredRequestData('showTeamBets', [], $this->module->getName());
        if (0 == $teamQuestionUid) {
            return '';
        }

        // Gehört die Question zum aktuellen Betset
        $teamQuestion = tx_rnbase::makeInstance(TeamQuestion::class, $teamQuestionUid);
        if ($teamQuestion->getBetSetUid() != $currBetSet->getUid()) {
            return '';
        }

        $options = ['module' => $this->module];
        $lister = tx_rnbase::makeInstance(TeamBetLister::class, $this->module, $options);
        $lister->setTeamQuestionUid($teamQuestionUid);

        $list = $lister->getResultList();
        $out = $list['pager']."\n".$list['table'];
        $out .= $this->getFormTool()->createSubmit('showTeamBets[0]', '###LABEL_CLOSE###');
        if (!$currBetSet->isFinished()) {
            $out .= $this->getFormTool()->createSubmit('resetTeamBets['.$teamQuestion->getUid().']', '###LABEL_RESETBETS###', $GLOBALS['LANG']->getLL('msg_resetbets'));
        }

        $out .= '<hr />';
        $headline = strip_tags($teamQuestion->getQuestion());

        return $this->module->getDoc()->section($headline, $out, 0, 1, IModFunc::ICON_INFO);
    }

    /**
     * Show a list of bets for a team question.
     *
     * @param BetSet $currBetSet
     *
     * @return string
     */
    private function handleResetTeamBets(BetSet $currBetSet)
    {
        $data = T3General::_GP('resetTeamBets');
        if (!is_array($data)) {
            return '';
        }
        $itemid = key($data);
        ServiceRegistry::getTeamBetService()->resetTeamBets($itemid);

        return '';
    }

    /**
     * Liefert das FormTool.
     *
     * @return ToolBox
     */
    private function getFormTool()
    {
        return $this->module->getFormTool();
    }
}
