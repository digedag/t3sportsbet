<?php

use Sys25\RnBase\Backend\Module\IModFunc;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Backend\Utility\Tables;
use Sys25\RnBase\Utility\T3General;
use Sys25\T3sportsbet\Model\BetSet;
use System25\T3sports\Model\Competition;
use System25\T3sports\Module\Utility\Selector;
use System25\T3sports\Utility\MatchTableBuilder;
use System25\T3sports\Utility\ServiceRegistry;

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
 * Search matches from competitions
 * We to it by showing to select boxes: one for competition and the other for round.
 */
class tx_t3sportsbet_mod1_matchsearcher
{
    private $mod;

    private $data;

    private $SEARCH_SETTINGS;

    /** @var Selector */
    private $selector;

    /** @var int */
    private $current_round;

    /** @var BetSet */
    private $currentRound;

    /** @var Competition */
    private $currComp;
    private $options;
    private $formTool;
    private $resultSize;
    private $competitions;

    public function __construct(IModule $mod, BetSet $currentRound, array $options = [])
    {
        $this->init($mod, $currentRound, $options);
    }

    private function getModule()
    {
        return $this->mod;
    }

    /**
     * @param IModule $mod
     * @param array $options
     */
    private function init(IModule $mod, BetSet $currentRound, array $options)
    {
        $this->options = $options;
        $this->mod = $mod;
        $this->currentRound = $currentRound;
        $this->options['currentRound'] = $currentRound;
        $this->options['pid'] = $this->mod->getPid();
        $this->formTool = $this->mod->getFormTool();
        $this->resultSize = 0;
        $this->data = T3General::_GP('searchdata');
        $this->competitions = $options['competitions'];
        $this->selector = tx_rnbase::makeInstance(Selector::class);
        $this->selector->init($this->getModule()->getDoc(), $this->getModule());
        // $this->selector->init($mod->doc, $mod->MCONF['name']);
        if (!isset($options['nopersist'])) {
            $this->SEARCH_SETTINGS = BackendUtility::getModuleData([
                'searchterm' => '',
            ], $this->data, $this->mod->getName());
        } else {
            $this->SEARCH_SETTINGS = $this->data;
        }
    }

    /**
     * Liefert das Suchformular.
     * Hier die beiden Selectboxen anzeigen.
     *
     * @param string $label Alternatives Label
     *
     * @return string
     */
    public function getSearchForm($label = '')
    {
        global $LANG;
        $out = '';
        // Wir zeigen zwei Selectboxen an
        $this->currComp = $this->selector->showLeagueSelector($out, $this->mod->id, $this->competitions);
        if (!$this->currComp) {
            return $out.$this->mod->getDoc()->section('Info:', $LANG->getLL('msg_no_competition_in_betgame'), 0, 1, \tx_rnbase_mod_IModFunc::ICON_WARN);
        }
        // $out.=$this->mod->doc->spacer(5);

        $rounds = $this->currComp->getRounds();
        if (!count($rounds)) {
            $out .= $LANG->getLL('msg_no_round_in_competition');

            return $out;
        }
        // Jetzt den Spieltag wählen lassen
        $this->current_round = $this->selector->showRoundSelector($out, $this->mod->id, $this->currComp);

        $out .= '<div style="clear:both" />';

        return $out;
    }

    public function getResultList()
    {
        $content = '';
        if (!is_object($this->currComp)) {
            return '';
        }

        // Mit Matchtable nach Spielen suchen
        $matchTable = $this->getMatchTable();
        $matchTable->setCompetitions($this->currComp->getUid());
        $matchTable->setRounds(is_object($this->current_round) ? $this->current_round->getUid() : $this->current_round);
        if (isset($this->options['ignoreDummies'])) {
            $matchTable->setIgnoreDummy();
        }
        $fields = $options = [];
        $options['orderby']['MATCH.DATE'] = 'ASC';
        $matchTable->getFields($fields, $options);
        $service = ServiceRegistry::getMatchService();
        $matches = $service->search($fields, $options);
        $this->resultSize = count($matches);
        $label = $this->resultSize.' '.((1 == $this->resultSize) ? $GLOBALS['LANG']->getLL('msg_found_match') : $GLOBALS['LANG']->getLL('msg_found_matches'));
        $this->showMatches($content, $label, $matches);

        return $content;
    }

    /**
     * Liefert die Anzahl der gefunden Datensätze.
     * Funktioniert natürlich erst, nachdem die Ergebnisliste abgerufen wurde.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->resultSize;
    }

    public function showMatches(&$content, $headline, &$matches)
    {
        if (empty($matches)) {
            $out = '<p><strong>'.$GLOBALS['LANG']->getLL('msg_no_matches_in_betset').'</strong></p><br/>';
            $content .= $this->mod->getDoc()->section($headline.':', $out, 0, 1, IModFunc::ICON_FATAL);

            return;
        }

        $decor = tx_rnbase::makeInstance('tx_t3sportsbet_util_MatchDecorator', $this->mod, $this->currentRound);
        $columns = [
            'uid' => [
                'title' => 'label_uid',
                'decorator' => $decor,
            ],
            'date' => [
                'title' => 'tx_cfcleague_games.date',
                'decorator' => $decor,
            ],
            'home' => [
                'title' => 'tx_cfcleague_games.home',
                'method' => 'getHomeNameShort',
            ],
            'guest' => [
                'title' => 'tx_cfcleague_games.guest',
                'method' => 'getGuestNameShort',
            ],
            'competition' => [
                'title' => 'label_group',
                'decorator' => $decor,
            ],
            'status' => [
                'title' => 'tx_cfcleague_games.status',
                'method' => 'getStateName',
            ],
            'result' => [
                'method' => 'getResult',
                'title' => 'label_result',
            ],
        ];

        global $LANG;
        $LANG->includeLLFile('EXT:cfc_league/Resources/Private/Language/locallang_db.xlf');

        $tables = tx_rnbase::makeInstance(Tables::class);
        $rows = $tables->prepareTable($matches, $columns, $this->formTool, $this->options);
        $out = $tables->buildTable($rows[0], $rows[1]);
        $content .= '<h3>'.$headline.'</h3>';
        $content .= $out;
    }

    /**
     * @return MatchTableBuilder
     */
    private function getMatchTable()
    {
        return tx_rnbase::makeInstance(MatchTableBuilder::class);
    }
}
