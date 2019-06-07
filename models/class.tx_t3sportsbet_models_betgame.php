<?php
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
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_model_base');

/**
 * Model for a betgame.
 */
class tx_t3sportsbet_models_betgame extends tx_rnbase_model_base
{

    private static $instances = array();

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
     * @return tx_t3sportsbet_models_betgame
     */
    public static function getBetgameInstance($uid)
    {
        $uid = intval($uid);
        if (! uid) {
            throw new Exception('Invalid uid for betgame');
        }
        if (! is_object(self::$instances[$uid])) {
            self::$instances[$uid] = new tx_t3sportsbet_models_betgame($uid);
        }
        return self::$instances[$uid];
    }

    public function getName()
    {
        return $this->getProperty('name');
    }

    /**
     *
     * @return boolean
     */
    public function isDrawIfExtraTime()
    {
        return $this->getProperty('draw_if_extratime') > 0;
    }

    /**
     *
     * @return boolean
     */
    public function isDrawIfPenalty()
    {
        return $this->getProperty('draw_if_penalty') > 0;
    }

    /**
     *
     * @return boolean
     */
    public function isIgnoreGreenTable()
    {
        return $this->getProperty('ignore_greentable') > 0;
    }

    /**
     * Points for exact bet
     *
     * @return int
     */
    public function getPointsAccurate()
    {
        return $this->getProperty('points_accurate');
    }

    /**
     * Points for tendency bet
     *
     * @return int
     */
    public function getPointsGoalsDiff()
    {
        return intval($this->getProperty('points_goalsdiff'));
    }

    /**
     * Points for tendency bet
     *
     * @return int
     */
    public function getPointsTendency()
    {
        return $this->getProperty('points_tendency');
    }

    /**
     * Minutes to close bets before match kick off
     *
     * @return int
     */
    public function getLockMinutes()
    {
        return intval($this->getProperty('lockminutes'));
    }

    /**
     * Returns the competition for a static bet game
     *
     * @return array of tx_cfcleague_models_Competition
     */
    public function getCompetitions()
    {
        $ret = [];
        tx_rnbase::load('tx_cfcleague_models_Competition');
        $uids = $this->getProperty('competition');
        if ($uids) {
            $uids = \Tx_Rnbase_Utility_Strings::intExplode(',', $uids);
            foreach ($uids as $uid) {
                $ret[] = tx_cfcleague_models_Competition::getCompetitionInstance($uid);
            }
        }
        return $ret;
    }

    /**
     * Returns the page UID
     *
     * @return int
     */
    public function getPid()
    {
        return $this->getProperty('pid');
    }

    /**
     * Returns an array of existing bet sets
     *
     * @return array of tx_t3sportsbet_models_betset
     */
    public function getBetSets()
    {
        $fields = $options = [];
        $fields['BETSET.BETGAME'][OP_EQ_INT] = $this->getUid();
        $options['orderby']['BETSET.ROUND'] = 'asc';

        $service = tx_t3sportsbet_util_serviceRegistry::getBetService();
        return $service->searchBetSet($fields, $options);
    }

    /**
     * Returns the number of betsets
     *
     * @return int
     */
    public function getBetSetSize()
    {
        $fields = $options = [];
        $fields['BETSET.BETGAME'][OP_EQ_INT] = $this->getUid();
        $options['count'] = '1';

        $service = tx_t3sportsbet_util_serviceRegistry::getBetService();
        return $service->searchBetSet($fields, $options);
    }
}

