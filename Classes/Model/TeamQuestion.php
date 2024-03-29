<?php

namespace Sys25\T3sportsbet\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2017 Rene Nitzsche (rene@system25.de)
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

use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Utility\Dates;
use Sys25\RnBase\Utility\Strings;

/**
 * Model for a team question.
 */
class TeamQuestion extends BaseModel
{
    private $teamUids;

    public function getTableName()
    {
        return 'tx_t3sportsbet_teamquestions';
    }

    /**
     * Returns the betset.
     *
     * @return BetSet
     */
    public function getBetSet()
    {
        return BetSet::getBetsetInstance($this->getBetSetUid());
    }

    /**
     * Returns the uid of betset.
     *
     * @return int
     */
    public function getBetSetUid()
    {
        return $this->getProperty('betset');
    }

    /**
     * Whether or not this team question is still open.
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->getOpenUntilTstamp() > time() && !$this->getTeamUid();
    }

    /**
     * Return the question string.
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->getProperty('question');
    }

    public function getOpenUntil()
    {
        return $this->getProperty('openuntil');
    }

    public function getPoints()
    {
        return $this->getProperty('points');
    }

    /**
     * Returns the uid of winning team.
     *
     * @return string comma separated uids
     */
    public function getTeamUid()
    {
        return $this->getProperty('team');
    }

    public function getOpenUntilTstamp()
    {
        return Dates::datetime_mysql2tstamp($this->getOpenUntil());
    }

    /**
     * Whether or not the given bet wins.
     *
     * @param TeamBet $bet
     *
     * @return bool
     */
    public function isWinningBet($bet)
    {
        if (!is_array($this->teamUids)) {
            $this->teamUids = Strings::intExplode(',', $this->getTeamUid());
            $this->teamUids = array_flip($this->teamUids);
        }

        return array_key_exists($bet->getTeamUid(), $this->teamUids);
    }
}
