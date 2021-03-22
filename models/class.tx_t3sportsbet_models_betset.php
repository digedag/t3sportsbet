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
tx_rnbase::load('tx_rnbase_model_base');
tx_rnbase::load('tx_t3sportsbet_models_betgame');

/**
 * Model for a betset.
 */
class tx_t3sportsbet_models_betset extends tx_rnbase_model_base
{
    private static $instances = array();

    public function getTableName()
    {
        return 'tx_t3sportsbet_betsets';
    }

    /**
     * Returns the betgame.
     *
     * @return tx_t3sportsbet_models_betgame
     */
    public function getBetgame()
    {
        return tx_t3sportsbet_models_betgame::getBetgameInstance($this->getProperty('betgame'));
    }

    /**
     * Returns the bet state of a match.
     * This can be OPEN, CLOSED or FINISHED
     * OPEN -> new bets are possible
     * CLOSED -> bets are not possible, but not analyzed
     * FINISHED -> bets are analyzed.
     *
     * @param tx_cfcleaguefe_models_match $match
     */
    public function getMatchState($match)
    {
        // Das Spiel ist finished, wenn es ausgewertet und in die Tipstatistik des Users
        // aufgenommen wurde -> Es hängt am User
        // TODO: Wir sollten das über den Tip des Users ermitteln
        if ($this->isFinished() || $match->isFinished()) {
            return 'FINISHED';
        }
        $state = 'OPEN';
        $now = tx_t3sportsbet_util_library::getNow();
        $lock = $this->getBetgame()->getLockMinutes() * 60;

        $matchDate = $match->getProperty('date');
        if ($matchDate <= ($now + $lock) || $match->isRunning()) {
            $state = 'CLOSED';
        }

        return $state;
    }

    /**
     * Returns all matches of this bet set.
     *
     * @return array of tx_cfcleaguefe_models_match
     */
    public function getMatches()
    {
        $service = tx_cfcleaguefe_util_ServiceRegistry::getMatchService();
        $fields['BETSETMM.UID_LOCAL'][OP_EQ_INT] = $this->getUid();
        $options['orderby']['BETSETMM.SORTING'] = 'asc';

        return $service->search($fields, $options);
    }

    /**
     * Returns the bet for a match.
     *
     * @param tx_cfcleaguefe_models_match $match
     * @param tx_t3users_models_feuser $feuser
     */
    public function getBet($match, $feuser)
    {
        $service = tx_t3sportsbet_util_serviceRegistry::getBetService();

        return $service->getBet($this, $match, $feuser);
    }

    /**
     * Returns the number of bets for a match in this betset.
     *
     * @param tx_cfcleaguefe_models_match $match
     *
     * @return int
     */
    public function getBetCount($match)
    {
        $service = tx_t3sportsbet_util_serviceRegistry::getBetService();
        $fields['BET.BETSET'][OP_EQ_INT] = $this->getUid();
        $fields['BET.T3MATCH'][OP_EQ_INT] = $match->getUid();
        $options['count'] = 1;
        // $options['debug'] = 1;
        return $service->searchBet($fields, $options);
    }

    public function getName()
    {
        return $this->getProperty('round_name');
    }

    /**
     * Whether or not bets can be made to this betset.
     *
     * @return bool
     */
    public function isFinished()
    {
        return 2 == $this->getProperty('status');
    }

    public function getStatus()
    {
        return $this->getProperty('status');
    }

    /**
     * Liefert die Instance mit der übergebenen UID.
     * Die Daten werden gecached, so daß
     * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
     *
     * @param int $uid
     *
     * @return tx_t3sportsbet_models_betset
     */
    public static function getBetsetInstance($uid)
    {
        $uid = intval($uid);
        if (!$uid) {
            throw new Exception('Invalid uid for betset');
        }
        if (!is_object(self::$instances[$uid])) {
            self::$instances[$uid] = new self($uid);
        }

        return self::$instances[$uid];
    }
}
