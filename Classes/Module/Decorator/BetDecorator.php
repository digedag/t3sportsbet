<?php

namespace Sys25\T3sportsbet\Module\Decorator;

use Sys25\RnBase\Domain\Repository\FeUserRepository;
use System25\T3sports\Model\Fixture;
use System25\T3sports\Model\Repository\MatchRepository;

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
 * Diese Klasse ist für die Darstellung von Tips im Backend verantwortlich.
 */
class BetDecorator
{
    private $feuserRepo;
    private $matchRepo;
    private $formTool;

    public function __construct()
    {
        $this->feuserRepo = new FeUserRepository();
        $this->matchRepo = new MatchRepository();
    }

    public function setFormTool($formTool)
    {
        $this->formTool = $formTool;
    }

    public function format($value, $colName, $record, $model)
    {
        $ret = $value;
        if ('tstamp' == $colName) {
            $ret = date('H:i d.m.Y', $value);
        } elseif ('t3matchresult' == $colName) {
            if (is_object($value)) {
                /** @var Fixture $match */
                $match = $this->matchRepo->findByUid($value->getProperty('t3match'));
                $ret = $match->getResult();
            }
        } elseif ('t3match' == $colName) {
            /** @var Fixture $match */
            $match = $this->matchRepo->findByUid($value);
            $ret = $match->getHomeNameShort().' - '.$match->getGuestNameShort();
            $ret .= $this->formTool->createEditLink('tx_cfcleague_games', $match->getUid(), '');
        } elseif ('fe_user' == $colName) {
            $feuser = $this->feuserRepo->getInstance($value);
            $ret = $feuser->getProperty('username');
            $ret .= $this->formTool->createEditLink('fe_users', $feuser->getUid(), '');
        }
        if ('uid' == $colName) {
            $ret = $value.' '.$this->formTool->createEditLink('tx_t3sportsbet_bets', $value, '');
        }
        if ('bet' == $colName) {
            $ret = $model->getProperty('goals_home').':'.$model->getProperty('goals_guest');
        }

        return $ret;
    }
}
