<?php

namespace Sys25\T3sportsbet\Module\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2019 Rene Nitzsche (rene@system25.de)
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
 * Die Klasse stellt Auswahlmenus zur Verfügung.
 */
class Selector
{
    private $doc;
    private $MCONF;

    private $formTool;

    private $modName;

    /**
     * Initialisiert das Objekt mit dem Template und der Modul-Config.
     */
    public function init($doc, \tx_rnbase_mod_IModule $module)
    {
        $this->doc = $doc;
        $this->MCONF['name'] = $module->getName(); // deprecated
        $this->modName = $module->getName();
        $this->module = $module;
        $this->formTool = \tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
        $this->formTool->init($this->doc, $module);
        \tx_rnbase_util_Misc::prepareTSFE();
    }

    /**
     * Darstellung der Select-Box mit allen Ligen der übergebenen Seite.
     * Es wird auf die aktuelle Liga eingestellt.
     *
     * @return \tx_t3sportsbet_models_betgame den aktuellen Wettbewerb als Objekt oder 0
     */
    public function showGameSelector(&$content, $pid, $games = 0)
    {
        // Wenn vorhanden, nehmen wir die übergebenen Wettbewerbe, sonst schauen wir auf der aktuellen Seite nach
        $games = $games ? $games : $this->findGames($pid);

        $objGames = $entries = [];
        foreach ($games as $game) {
            $objGames[$game->getUid()] = $game;
            $entries[$game->getUid()] = $game->getName();
        }
        if (!count($entries)) {
            return 0;
        }

        $menuData = $this->getFormTool()->showMenu($pid, 'game', $this->modName, $entries);
        $menu = $menuData['menu'];

        // In den Content einbauen
        // Zusätzlich noch einen Edit-Link setzen
        if ($menu) {
            $links = [];
            $links[] = $this->getFormTool()->createEditLink('tx_t3sportsbet_betgames', $menuData['value'], '');
            $links[] = $this->getFormTool()->createNewLink('tx_t3sportsbet_betgames', $pid, '');
            $content .= $this->renderSelector($menu, $links);
        }

        // Aktuellen Wert als Objekt zurückgeben
        return $menuData['value'] ? $objGames[$menuData['value']] : 0;
    }

    /**
     * Returns all rounds of current bet game.
     *
     * @param string $content
     * @param int $pid
     * @param \tx_t3sportsbet_models_betgame $game
     *
     * @return \tx_t3sportsbet_models_betset
     */
    public function showRoundSelector(&$content, $pid, $game)
    {
        $rounds = $game->getBetSets();
        $idxRounds = [];
        $entries = [];
        foreach ($rounds as $round) {
            $idxRounds[$round->getUid()] = $round;
            $entries[$round->getUid()] = $round->getName().' ('.$GLOBALS['LANG']->getLL('tx_t3sportsbet_module.betStatus_'.$round->getStatus()).')';
        }
        $menuData = $this->getFormTool()->showMenu($pid, 'betset', $this->modName, $entries);

        if ($menuData['value'] > 0) {
            $menu = $menuData['menu'];
            if ($menu) {
                $links = [];
                $links[] = $this->getFormTool()->createEditLink('tx_t3sportsbet_betsets', $menuData['value'], '');
                $params = [];
                $params['defvals'] = [
                    'tx_t3sportsbet_betsets' => [
                        'betgame' => $game->getUid(),
                        'round' => ($game->getBetSetSize() + 1),
                    ],
                ];
                $params['title'] = '###LABEL_CREATE_BETSET###';
                $links[] = $this->getFormTool()->createNewLink('tx_t3sportsbet_betsets', $pid, '', $params);
                $menu = $this->renderSelector($menu, $links);
            }
        } else {
            $menu .= $GLOBALS['LANG']->getLL('msg_no_betset_found');
        }

        // In den Content einbauen
        // Spielrunden sind keine Objekte, die bearbeitet werden können
        $content .= $menu;
        if (count($idxRounds)) {
            return $menuData['value'] ? $idxRounds[$menuData['value']] : 0;
        }
    }

    private function renderSelector($menu, array $links = [])
    {
        return '
<div class="row">
<div class="selector col-sm-4">'.$menu.'</div>'.(empty($links) ? '' : '<div class="links col-sm-4">'.implode(' ', $links).'</div>').
        '</div>';
    }

    /**
     * Liefert die Tipspiele der aktuellen Seite.
     *
     * @return array mit Rows
     */
    private function findGames($pid)
    {
        $options = [];
        $options['where'] = 'pid="'.$pid.'"';
        $options['orderby'] = 'sorting';
        $options['wrapperclass'] = 'tx_t3sportsbet_models_betgame';

        return \Tx_Rnbase_Database_Connection::getInstance()->doSelect('*', 'tx_t3sportsbet_betgames', $options, 0);
    }

    /**
     * @return \tx_rnbase_util_FormTool
     */
    private function getFormTool()
    {
        return $this->formTool;
    }
}
