<?php
namespace Sys25\T3sportsbet\Module\Controller\BetGame;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2018 Rene Nitzsche (rene@system25.de)
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
 * Die Klasse verwaltet die Erstellung Teams für Wettbewerbe
 */
class ShowBetSet
{
    protected $doc;
    /**
     * @var \tx_rnbase_mod_IModule
     */
    protected $module;

    /**
     * @var \tx_t3sportsbet_models_betset
     */
    protected $currentRound;
    /**
     * @var \tx_t3sportsbet_models_betgame
     */
    protected $currentGame;

    /**
     * Verwaltet die Erstellung von Spielplänen von Ligen
     *
     * @param \tx_rnbase_mod_IModule $module
     * @param \tx_t3sportsbet_models_betset $currentRound
     * @param \tx_t3sportsbet_models_betgame $currentGame
     */
    public function __construct($module, $currentRound, $currentGame)
    {
        $this->module = $module;
        $this->doc = $module->getDoc();

        $this->formTool = $module->getFormTool();
        $this->currentGame = $currentGame;
        $this->currentRound = $currentRound;
    }
    /**
     *
     * @return string
     */
    public function handleRequest()
    {
        // Zuerst mal müssen wir die passende Liga auswählen lassen:
        // Entweder global über die Datenbank oder die Ligen der aktuellen Seite
        $content = '';
        $content .= $this->handleShowBets($this->currentRound);
        $content .= $this->handleResetBets($this->currentRound);
        $content .= $this->handleSaveBetSet($this->currentRound);
        $content .= $this->handleAnalyzeBets($this->currentGame);
        return $content;
    }

    /**
     *
     * @return string
     */
    public function show()
    {
        $currBetSet = $this->currentRound;
        $options = [];
        $options['linker'][] = \tx_rnbase::makeInstance('tx_t3sportsbet_mod1_link_MatchBets');
        $options['module'] = $this->module;

        $pasteButton = \tx_t3sportsbet_mod1_handler_MatchMove::getInstance()->makePasteButton($this->currentRound, $this->module);
        if($pasteButton) {
            $out .= $this->doc->section('Info:',$pasteButton,0,1,ICON_INFO);
        }

        /* @var $searcher \tx_t3sportsbet_mod1_matchsearcher */
        $searcher = \tx_rnbase::makeInstance(
            'tx_t3sportsbet_mod1_matchsearcher',
            $this->module,
            $this->currentRound,
            $options
        );

        $searcher->showMatches($out, '###LABEL_MATCHLIST###', $currBetSet->getMatches());

        $out .= $this->formTool->getTCEForm()->getSoloField('tx_t3sportsbet_betsets', $currBetSet->getProperty(), 'status');
        $out .= $this->formTool->createSubmit('savebetset', '###LABEL_SAVE###');

//        $out .= $this->module->getDoc()->spacer(10);
        $out .= '<p>'.$this->formTool->createSubmit('analyzebets', '###LABEL_ANALYZEBETS###').'</p>';
        return $out;
    }

    /**
     * Show a list of bets for a match
     *
     * @param \tx_t3sportsbet_models_betset $currBetSet
     */
    protected function handleShowBets($currBetSet) {
        $matchUids = $this->getFormTool()->getStoredRequestData('showBets', [], $this->module->getName());
        if($matchUids == 0) {
            return '';
        }

        $options = ['module' => $this];
        $lister = \tx_rnbase::makeInstance('tx_t3sportsbet_mod1_lister_MatchBet', $this->module, $options);
        $lister->setMatchUids($matchUids);
        $lister->setBetSetUid($currBetSet->getUid());

        $list = $lister->getResultList();
        $out .= $list['pager']."\n".$list['table'];
        $out .= $this->getFormTool()->createSubmit('showBets[0]', $GLOBALS['LANG']->getLL('label_close'));
        return $this->doc->section($GLOBALS['LANG']->getLL('label_betlist').':',$out,0,1,ICON_INFO);
    }
    /**
     * Reset all bets for a given match.
     *
     * @param \tx_t3sportsbet_models_betset $currBetSet
     */
    protected function handleResetBets($currBetSet) {
        $matchUids = \Tx_Rnbase_Utility_T3General::_GP('resetBets');
        if(!is_array($matchUids)) return;

        $tce = \Tx_Rnbase_Database_Connection::getInstance()->getTCEmain();
        $details = 'T3sportsbet: All bets for match with uid %s of betset with uid %s were reset.';
        $matchUids = array_keys($matchUids);
        foreach($matchUids As $uid) {
            // Jetzt alle Tips für das Spiel suchen in dieser Tiprunde suchen und zurücksetzen
            $srv = \tx_t3sportsbet_util_serviceRegistry::getBetService();
            $srv->resetBets($currBetSet, $uid);

            //$tce->BE_USER->writelog($type,$action,$error,$details_nr,$details,$data,$table,$recuid,$recpid,$event_pid,$NEWid);
            $data = [$uid, $currBetSet->uid];
            $tce->BE_USER->writelog(1,2,0,0,$details,$data);
        }
    }

    /**
     * Show form to add matches to betset
     *
     * @param \tx_t3sportsbet_models_betset $currBetSet
     * @return string
     */
    protected function handleSaveBetSet($currBetSet) {
        $out = '';
        $button = strlen(\Tx_Rnbase_Utility_T3General::_GP('savebetset')) > 0;
        if($button) {
            $data = \Tx_Rnbase_Utility_T3General::_GP('data');
            $tce = \Tx_Rnbase_Database_Connection::getInstance()->getTCEmain($data);
            $tce->process_datamap();
            $out .= $GLOBALS['LANG']->getLL('msg_betset_saved');
            $currBetSet->reset();
        }
        return $out;
    }
    /**
     * Starts analysis of betgame if button was pressed.
     * @param \tx_t3sportsbet_models_betgame $betGame
     * @return string
     */
    protected function handleAnalyzeBets($betGame) {
        //
        $out = '';
        $button = strlen(\Tx_Rnbase_Utility_T3General::_GP('analyzebets')) > 0;
        if($button) {
            $betsUpdated = \tx_t3sportsbet_util_serviceRegistry::getBetService()->analyzeBets($betGame);
            $betsUpdated += \tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->analyzeBets($betGame);
            \tx_t3sportsbet_util_serviceRegistry::getBetService()->updateBetsetResultsByGame($betGame);
            $out .= $GLOBALS['LANG']->getLL('msg_bets_finished') . ':' . $betsUpdated;
        }
        return $out;
    }

    /**
     * Returns the formtool
     *
     * @return \tx_rnbase_util_FormTool
     */
    protected function getFormTool()
    {
        return $this->formTool;
    }
}
