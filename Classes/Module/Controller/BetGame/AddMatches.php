<?php

namespace Sys25\T3sportsbet\Module\Controller\BetGame;

/**
 * *************************************************************
 * Copyright notice.
 *
 * (c) 2008-2019 Rene Nitzsche (rene@system25.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

/**
 * Spiele einer Tiprunde hinzufügen.
 */
class AddMatches
{
    /**
     * @var \tx_rnbase_mod_IModule
     */
    private $mod;

    /**
     * @var \tx_t3sportsbet_models_betset
     */
    protected $currentRound;

    /**
     * @param \tx_rnbase_mod_IModule $mod
     * @param \tx_t3sportsbet_models_betset $currBetSet
     */
    public function __construct($mod, $currBetSet)
    {
        $this->mod = $mod;
        $this->currentRound = $currBetSet;
    }

    /**
     * Ausführung des Requests.
     *
     * @param \tx_t3sportsbet_models_betset $currBetSet
     *
     * @return string
     */
    public function show()
    {
        $currBetSet = $this->currentRound;
        $out .= $this->handleAddCompetition();
        $competitions = $currBetSet->getBetgame()->getCompetitions();
        if (!count($competitions)) {
            $out .= $this->handleNoCompetitions($currBetSet);
        } else {
            $out .= $this->handleAddMatches($currBetSet);
            $out .= $this->showAddMatches($currBetSet, $competitions);
        }

        return $out;
    }

    /**
     * Sollte aufgerufen werden, wenn keine Wettberbe im Tipspiel zugeordnet sind.
     *
     * @param \tx_t3sportsbet_models_betset $currBetSet
     */
    private function handleNoCompetitions($currBetSet)
    {
        $out .= $this->mod->getDoc()->section('Info:', $GLOBALS['LANG']->getLL('msg_no_competition_in_betgame'), 0, 1, \tx_rnbase_mod_IModFunc::ICON_WARN);
        $out .= $this->mod->getDoc()->spacer(10);
        $out .= $this->getFormTool()->form->getSoloField('tx_t3sportsbet_betgames', $currBetSet->getBetgame()->getProperty(), 'competition');
        $out .= $this->getFormTool()->createSubmit('updateBetgame', $GLOBALS['LANG']->getLL('btn_update'));

        return $out;
    }

    /**
     * Liefert das FormTool.
     *
     * @return \tx_rnbase_util_FormTool
     */
    private function getFormTool()
    {
        return $this->mod->getFormTool();
    }

    /**
     * Shows a list of matches.
     *
     * @param \tx_t3sportsbet_models_betset $currBetSet
     * @param array $competitions
     *
     * @return string
     */
    protected function showAddMatches($currBetSet, $competitions)
    {
        $options = ['checkbox' => 1];

        $srv = \tx_t3sportsbet_util_serviceRegistry::getBetService();
        $matches = $srv->findMatchUids($currBetSet->getBetgame());
        foreach ($matches as $match) {
            $options['dontcheck'][$match['uid']] = $GLOBALS['LANG']->getLL('msg_match_already_joined');
        }
        $options['competitions'] = $competitions;
        $options['ignoreDummies'] = 1;
        /* @var $searcher \tx_t3sportsbet_mod1_matchsearcher */
        $searcher = \tx_rnbase::makeInstance(
            'tx_t3sportsbet_mod1_matchsearcher',
            $this->mod,
            $currBetSet,
            $options
        );

        $out = $this->mod->getDoc()->spacer(15);
        $out .= $searcher->getSearchForm();
        $out .= $searcher->getResultList();
        if ($searcher->getSize()) {
            // Button für Zuordnung
            $out .= $this->mod->getFormTool()->createSubmit('match2betset', '###LABEL_JOIN_MATCHES###', $GLOBALS['LANG']->getLL('msg_join_matches'));
        }

        return $out;
    }

    /**
     * Add matches to a betset.
     *
     * @return string
     */
    private function handleAddCompetition()
    {
        $buttonPressed = strlen(\Tx_Rnbase_Utility_T3General::_GP('updateBetgame')) > 0; // Wurde der Submit-Button gedrückt?
        if ($buttonPressed) {
            $data = \Tx_Rnbase_Utility_T3General::_GP('data');
            $tce = \Tx_Rnbase_Database_Connection::getInstance()->getTCEmain($data, []);
            $tce->process_datamap();
        }
    }

    /**
     * Add matches to a betset.
     *
     * @param \tx_t3sportsbet_models_betset $currBetSet
     *
     * @return string
     */
    private function handleAddMatches($currBetSet)
    {
        $out = '';
        $match2set = strlen(\Tx_Rnbase_Utility_T3General::_GP('match2betset')) > 0; // Wurde der Submit-Button gedrückt?
        if ($match2set) {
            $matchUids = \Tx_Rnbase_Utility_T3General::_GP('checkEntry');
            if (!is_array($matchUids) || !count($matchUids)) {
                $out = $GLOBALS['LANG']->getLL('msg_no_match_selected').'<br/>';
            } else {
                // Die Spiele setzen
                $service = \tx_t3sportsbet_util_serviceRegistry::getBetService();
                $cnt = $service->addMatchesTCE($currBetSet, $matchUids);
                $out = $cnt.' '.$GLOBALS['LANG']->getLL('msg_matches_added');
            }
        }

        return (strlen($out)) ? $this->mod->getDoc()->section('###LABEL_INFO###'.':', $out, 0, 1, \tx_rnbase_mod_IModFunc::ICON_INFO) : '';
    }
}
