<?php

use Sys25\RnBase\Domain\Repository\FeUserRepository;
use Sys25\T3sportsbet\Model\TeamQuestion;
use System25\T3sports\Model\Team;

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

/**
 * Show team bets in BE module.
 */
class tx_t3sportsbet_mod1_decorator_TeamBet
{
    private $mod;
    private $feuserRepo;

    public function __construct($mod)
    {
        $this->mod = $mod;
        $this->feuserRepo = new FeUserRepository();
    }

    /**
     * Returns the module.
     *
     * @return tx_rnbase_mod_IModule
     */
    private function getModule()
    {
        return $this->mod;
    }

    /**
     * @param string $value
     * @param string $colName
     * @param array $record
     * @param TeamQuestion $item
     */
    public function format($value, $colName, $record, $item)
    {
        $ret = $value;
        if ('uid' == $colName) {
            $ret = $this->getModule()
                ->getFormTool()
                ->createEditLink('tx_t3sportsbet_teambets', $item->getUid(), '');
            $wrap = $item->getProperty('hidden') ? [
                '<strike>',
                '</strike>',
            ] : [
                '<strong>',
                '</strong>',
            ];
            $ret .= $wrap[0].$value.$wrap[1].'<br />';
        } elseif ('tstamp' == $colName) {
            $ret = date('H:i d.m.Y', $value);
        } elseif ('finished' == $colName) {
            $ret = $value ? '###LABEL_YES###' : '###LABEL_NO###';
        } elseif ('team' == $colName) {
            $team = tx_rnbase::makeInstance(Team::class, $value);
            $ret = $team->getName();
        } elseif ('feuser' == $colName) {
            $feuser = $this->feuserRepo->getInstance($value);
            $ret = $feuser->getProperty('username');
            $ret .= $this->getModule()
                ->getFormTool()
                ->createEditLink('fe_users', $feuser->getUid(), '');
        }

        return $ret;
    }
}
