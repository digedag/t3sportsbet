<?php

namespace Sys25\T3sportsbet\Module\Controller\BetGame;

use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Module\IModFunc;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Utility\T3General;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Module\Lister\MatchLister;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use tx_rnbase;
use tx_rnbase_mod_IModFunc;
use tx_rnbase_mod_IModule;

/**
 * *************************************************************
 * Copyright notice.
 *
 * (c) 2008-2023 Rene Nitzsche (rene@system25.de)
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
     * @var IModule
     */
    private $mod;

    /**
     * @var BetSet
     */
    protected $currentRound;

    /**
     * @param IModule $mod
     * @param BetSet $currBetSet
     */
    public function __construct($mod, $currBetSet)
    {
        $this->mod = $mod;
        $this->currentRound = $currBetSet;
    }

    /**
     * Ausführung des Requests.
     *
     * @param BetSet $currBetSet
     *
     * @return string
     */
    public function show()
    {
        $currBetSet = $this->currentRound;
        $out = $this->handleAddCompetition();
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
     * @param BetSet $currBetSet
     */
    private function handleNoCompetitions($currBetSet)
    {
        $out = $this->mod->getDoc()->section('Info:', $GLOBALS['LANG']->getLL('msg_no_competition_in_betgame'), 0, 1, IModFunc::ICON_WARN);
        $out .= $this->mod->getDoc()->spacer(10);
        $out .= $this->getFormTool()->form->getSoloField('tx_t3sportsbet_betgames', $currBetSet->getBetgame()->getProperty(), 'competition');
        $out .= $this->getFormTool()->createSubmit('updateBetgame', $GLOBALS['LANG']->getLL('btn_update'));

        return $out;
    }

    /**
     * Liefert das FormTool.
     *
     * @return ToolBox
     */
    private function getFormTool()
    {
        return $this->mod->getFormTool();
    }

    /**
     * Shows a list of matches.
     *
     * @param BetSet $currBetSet
     * @param array $competitions
     *
     * @return string
     */
    protected function showAddMatches($currBetSet, $competitions)
    {
        $options = ['checkbox' => 1];

        $srv = ServiceRegistry::getBetService();
        $matches = $srv->findMatchUids($currBetSet->getBetgame());
        foreach ($matches as $match) {
            $options['dontcheck'][$match['uid']] = $GLOBALS['LANG']->getLL('msg_match_already_joined');
        }
        $options['competitions'] = $competitions;
        $options['ignoreDummies'] = 1;
        /** @var MatchLister $searcher */
        $searcher = tx_rnbase::makeInstance(
            MatchLister::class,
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
        $buttonPressed = strlen(T3General::_GP('updateBetgame')) > 0; // Wurde der Submit-Button gedrückt?
        if ($buttonPressed) {
            $data = T3General::_GP('data');
            $tce = Connection::getInstance()->getTCEmain($data, []);
            $tce->process_datamap();
        }
    }

    /**
     * Add matches to a betset.
     *
     * @param BetSet $currBetSet
     *
     * @return string
     */
    private function handleAddMatches($currBetSet)
    {
        $out = '';
        $match2set = strlen(T3General::_GP('match2betset')) > 0; // Wurde der Submit-Button gedrückt?
        if ($match2set) {
            $matchUids = T3General::_GP('checkEntry');
            if (!is_array($matchUids) || !count($matchUids)) {
                $out = $GLOBALS['LANG']->getLL('msg_no_match_selected').'<br/>';
            } else {
                // Die Spiele setzen
                $service = ServiceRegistry::getBetService();
                $cnt = $service->addMatchesTCE($currBetSet, $matchUids);
                $out = $cnt.' '.$GLOBALS['LANG']->getLL('msg_matches_added');
            }
        }

        return (strlen($out)) ? $this->mod->getDoc()->section('###LABEL_INFO###:', $out, 0, 1, IModFunc::ICON_INFO) : '';
    }
}
