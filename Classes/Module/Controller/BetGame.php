<?php

namespace Sys25\T3sportsbet\Module\Controller;

use Exception;
use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Module\BaseModFunc;
use Sys25\RnBase\Backend\Module\IModFunc;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Backend\Utility\Tables;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Frontend\Marker\FormatUtil;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Utility\Logger;
use Sys25\RnBase\Utility\TYPO3;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Module\Handler\MatchMoveHandler;
use Sys25\T3sportsbet\Module\Utility\AddCompetitionWizard;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use tx_rnbase;

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
 * Die Klasse ist die Einstiegsklasse für das Modul "Tippspiel".
 */
class BetGame extends BaseModFunc
{
    /** @var \Sys25\T3sportsbet\Module\Utility\Selector */
    private $selector;
    public $betset;

    /**
     * Method getFuncId.
     *
     * @return string
     */
    protected function getFuncId()
    {
        return 'funcbetgame';
    }

    public function getModuleIdentifier()
    {
        return 'cfc_league';
    }

    public function init(IModule $module, $conf)
    {
        parent::init($module, $conf);
        if (!TYPO3::isTYPO121OrHigher()) {
            $GLOBALS['LANG']->includeLLFile('EXT:t3sportsbet/Resources/Private/Language/locallang_mod.xlf');
        }
    }

    /**
     * @param string $template
     * @param ConfigurationInterface $configurations
     * @param FormatUtil $formatter
     * @param ToolBox $formTool
     *
     * @return string
     */
    protected function getContent($template, &$configurations, &$formatter, $formTool)
    {
        global $LANG;
        $this->selector = tx_rnbase::makeInstance(\Sys25\T3sportsbet\Module\Utility\Selector::class);
        $this->selector->init($this->getModule()->getDoc(), $this->getModule());

        $content = '';
        $selector = '';
        // Anzeige der vorhandenen Tipspiele
        $currentGame = $this->selector->showGameSelector($selector, $this->getModule()->getPid());

        if (!$currentGame) {
            $content .= $this->getModule()
                ->getDoc()
                ->section('Info:', $LANG->getLL('msg_no_game_in_page'), 0, 1, IModFunc::ICON_WARN);
            $content .= '<p style="margin-top:5px; font-weight:bold;">'.$formTool->createNewLink('tx_t3sportsbet_betgames', $this->getModule()
                ->getPid(), $LANG->getLL('msg_create_new_game')).'</p>';

            return $content;
        }
        $content = '';
        $this->getModule()->setSelector($selector);

        $currentRound = $this->selector->showRoundSelector($selector, $this->getModule()->getPid(), $currentGame);
        if (!$currentRound) {
            /* @var $wizard AddCompetitionWizard */
            $wizard = tx_rnbase::makeInstance(AddCompetitionWizard::class);
            $content .= $wizard->handleRequest($this->getModule(), $currentGame);

            return $content;
        }
        $this->getModule()->setSelector($selector);

        // RequestHandler aufrufen.
        $content .= MatchMoveHandler::getInstance()->handleRequest($this->getModule());

        $menu = $formTool->showTabMenu($this->getModule()
            ->getPid(), 'bettools', $this->getModule()
            ->getName(), [
            '0' => $LANG->getLL('tab_control'),
            '1' => $LANG->getLL('tab_addmatches'),
            '2' => $LANG->getLL('tab_addteambets'),
            '3' => $LANG->getLL('tab_bets'),
        ]);

        $content .= $menu['menu'];

        try {
            $this->betset = $currentRound; // Nicht schön, aber so hat der Linker Zugriff

            $funcContent = '';
            switch ($menu['value']) {
                case 0:
                    $handler = tx_rnbase::makeInstance(\Sys25\T3sportsbet\Module\Controller\BetGame\ShowBetSet::class, $this->getModule(), $currentRound, $currentGame);
                    $funcContent .= $handler->handleRequest();
                    $funcContent .= $handler->show();

                    break;
                case 1:
                    $handler = tx_rnbase::makeInstance(\Sys25\T3sportsbet\Module\Controller\BetGame\AddMatches::class, $this->getModule(), $currentRound);
                    $funcContent .= $handler->show();

                    break;
                case 2:
                    $handler = tx_rnbase::makeInstance(\Sys25\T3sportsbet\Module\Controller\BetGame\AddTeamBets::class, $this->getModule(), $currentRound);
                    $funcContent .= $handler->handleRequest();
                    $funcContent .= $handler->show();

                    break;
                case 3:
                    $handler = tx_rnbase::makeInstance(\Sys25\T3sportsbet\Module\Controller\BetGame\ShowBets::class, $this->getModule(), $currentRound);
                    $funcContent .= $handler->show();
                    // $funcContent .= $this->showBets($currentRound);
                    break;
            }
            $funcContent .= $this->showInfobar($currentRound);
        } catch (Exception $e) {
            $msg = '<h2>FATAL ERROR: </h2><pre>';
            // $e->getMessage();
            $msg .= $e->__toString();
            $msg .= '</pre>';
            Logger::warn('Exception in BE module.', 't3sportsbet', [
                'Exception' => $e->getMessage(),
            ]);
            $content .= $msg;
        }
        $content .= $formTool->form->printNeededJSFunctions_top();
        $content .= '<div style="display: block; border: 1px solid #a2aab8; clear:both;"></div>';
        $content .= $funcContent;

        $content .= $formTool->form->printNeededJSFunctions();

        $modContent = Templates::getSubpart($template, '###MAIN###');
        $modContent = Templates::substituteMarkerArrayCached($modContent, [
            '###CONTENT###' => $content,
        ]);

        return $modContent;
    }

    /**
     * Shows some information about current betset.
     *
     * @param BetSet $currBetSet
     *
     * @return string
     */
    protected function showInfoBar(?BetSet $currBetSet)
    {
        $srv = ServiceRegistry::getBetService();
        $dates = $srv->getBetsetDateRange($currBetSet);
        if (!$dates) {
            return '';
        }
        $matchCnt = count($currBetSet->getMatches());
        $row = [];
        $row[] = ['###LABEL_BETSETINFO###'];
        $date = $dates['low'][0];
        $match = $dates['low'][1];
        $row[] = [
            '###LABEL_BETSETINFO_LOWDATE###',
            strftime('%d. %b %y %H:%M', $date).' ('.$match->getHomeNameShort().'-'.$match->getGuestNameShort().')',
        ];
        $date = $dates['high'][0];
        $match = $dates['high'][1];
        $row[] = [
            '###LABEL_BETSETINFO_HIGHDATE###',
            strftime('%d. %b %y %H:%M', $date).' ('.$match->getHomeNameShort().'-'.$match->getGuestNameShort().')',
        ];
        if ($dates['next']) {
            $date = $dates['next'][0];
            $match = $dates['next'][1];
            $row[] = [
                '###LABEL_BETSETINFO_NEXTDATE###',
                strftime('%d. %b %y %H:%M', $date).' ('.$match->getHomeNameShort().'-'.$match->getGuestNameShort().')',
            ];
        }
        $row[] = [
            '###LABEL_BETSETINFO_MATCHCOUNT###',
            $matchCnt,
        ];
        $row[] = [
            '###LABEL_BETSETINFO_USERCOUNT###',
            $srv->getResultSize($currBetSet->getUid()),
        ];
        $row[] = [
            '###LABEL_BETSETINFO_BETCOUNT###',
            $srv->getBetSize($currBetSet),
        ];

        /** @var $tables Tables */
        $tables = tx_rnbase::makeInstance(Tables::class);
        $out = $tables->buildTable($row, $this->getInfoTableLayout());

        return $out;
    }

    /**
     * Liefert das Layout für die Infotabelle.
     *
     * @return array
     */
    private function getInfoTableLayout()
    {
        $layout = [
            'table' => [
                '<table class="table table-striped table-hover table-condensed">', '</table>',
            ],
            '0' => [ // Format für 1. Zeile
                'tr' => ['<thead><tr class="">', '</tr></thead>'],
                'defCol' => ['<th colspan="2">', '</th>'],  // Format für jede Spalte in der 1. Zeile
            ],
            'defRow' => [ // Formate für alle Zeilen
                '0' => ['<th>', '</th>'], // Format für 1. Spalte in jeder Zeile
                'defCol' => ['<td>', '</td>'], // Format für jede Spalte in jeder Zeile
            ],
        ];

        return $layout;
    }
}
