<?php

namespace Sys25\T3sportsbet\Module\Decorator;

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

use Sys25\RnBase\Backend\Module\IModule;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Module\Handler\MatchMoveHandler;
use System25\T3sports\Model\Competition;
use System25\T3sports\Model\Fixture;

/**
 * Diese Klasse ist für die Darstellung von Spielen im Backend verantwortlich.
 */
class MatchDecorator
{
    protected $module;

    protected $currentRound;

    /**
     * @param IModule $module
     * @param BetSet $currentRound
     */
    public function __construct(IModule $module, BetSet $currentRound)
    {
        $this->module = $module;
        $this->currentRound = $currentRound;
    }

    /**
     * @return IModule
     */
    private function getModule()
    {
        return $this->module;
    }

    public function format($value, $colName, $record, $item)
    {
        $ret = $value;
        if ('uid' == $colName) {
            $ret .= $this->createMatchCutLink($item);
        } elseif ('date' == $colName) {
            $ret = date('H:i d.m.y', $value);
        } elseif ('competition' == $colName) {
            $comp = Competition::getCompetitionInstance($value);
            if (!is_object($comp) || !$comp->isValid()) {
                return '';
            }
            $group = $comp->getGroup();
            if (!is_object($group) || !$group->isValid()) {
                return '';
            }
            $name = (array_key_exists('shortname', $group->getProperty())) ? $group->getProperty('shortname') : '';
            $ret = strlen($name) ? $name : $group->getName();
        }

        return $ret;
    }

    /**
     * @param Fixture $item
     */
    private function createMatchCutLink($item)
    {
        return MatchMoveHandler::getInstance()->makeCutLink(
            $item,
            $this->currentRound,
            $this->getModule()
        );
    }
}
