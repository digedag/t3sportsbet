<?php

namespace Sys25\T3sportsbet\Model;

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

use Exception;
use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Utility\Strings;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use System25\T3sports\Model\Competition;

/**
 * Model for a betgame.
 */
class BetGame extends BaseModel
{
    private static $instances = [];

    public function getTableName()
    {
        return 'tx_t3sportsbet_betgames';
    }

    /**
     * Liefert die Instance mit der übergebenen UID.
     * Die Daten werden gecached, so daß
     * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
     *
     * @param int $uid
     *
     * @return self
     */
    public static function getBetgameInstance($uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            throw new Exception('Invalid uid for betgame');
        }
        if (!isset(self::$instances[$uid])) {
            self::$instances[$uid] = new self($uid);
        }

        return self::$instances[$uid];
    }

    public function getName()
    {
        return $this->getProperty('name');
    }

    /**
     * @return bool
     */
    public function isDrawIfExtraTime()
    {
        return $this->getProperty('draw_if_extratime') > 0;
    }

    /**
     * @return bool
     */
    public function isDrawIfPenalty()
    {
        return $this->getProperty('draw_if_penalty') > 0;
    }

    /**
     * @return bool
     */
    public function isIgnoreGreenTable()
    {
        return $this->getProperty('ignore_greentable') > 0;
    }

    /**
     * Points for exact bet.
     *
     * @return int
     */
    public function getPointsAccurate()
    {
        return $this->getProperty('points_accurate');
    }

    /**
     * Points for tendency bet.
     *
     * @return int
     */
    public function getPointsGoalsDiff()
    {
        return intval($this->getProperty('points_goalsdiff'));
    }

    /**
     * Points for tendency bet.
     *
     * @return int
     */
    public function getPointsTendency()
    {
        return $this->getProperty('points_tendency');
    }

    /**
     * Minutes to close bets before match kick off.
     *
     * @return int
     */
    public function getLockMinutes()
    {
        return intval($this->getProperty('lockminutes'));
    }

    /**
     * Returns the competition for a static bet game.
     *
     * @return Competition[]
     */
    public function getCompetitions()
    {
        $ret = [];
        $uids = $this->getProperty('competition');
        if ($uids) {
            $uids = Strings::intExplode(',', $uids);
            foreach ($uids as $uid) {
                $ret[] = Competition::getCompetitionInstance($uid);
            }
        }

        return $ret;
    }

    /**
     * Returns the page UID.
     *
     * @return int
     */
    public function getPid()
    {
        return $this->getProperty('pid');
    }

    /**
     * Returns an array of existing bet sets.
     *
     * @return Betset[]
     */
    public function getBetSets()
    {
        $fields = $options = [];
        $fields['BETSET.BETGAME'][OP_EQ_INT] = $this->getUid();
        $options['orderby']['BETSET.ROUND'] = 'asc';

        $service = ServiceRegistry::getBetService();

        return $service->searchBetSet($fields, $options);
    }

    /**
     * Returns the number of betsets.
     *
     * @return int
     */
    public function getBetSetSize()
    {
        $fields = $options = [];
        $fields['BETSET.BETGAME'][OP_EQ_INT] = $this->getUid();
        $options['count'] = '1';

        $service = ServiceRegistry::getBetService();

        return $service->searchBetSet($fields, $options);
    }
}
