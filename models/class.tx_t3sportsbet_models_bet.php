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

/**
 * Model for a bet.
 */
class tx_t3sportsbet_models_bet extends tx_rnbase_model_base
{
    public function getTableName()
    {
        return 'tx_t3sportsbet_bets';
    }

    /**
     * Returns the match.
     *
     * @return tx_cfcleaguefe_models_match
     */
    public function getMatch()
    {
        return tx_cfcleaguefe_models_match::getMatchInstance($this->getProperty('t3match'));
    }

    /**
     * Returns the betgame.
     *
     * @return tx_t3sportsbet_models_betset
     */
    public function getBetSet()
    {
        return tx_t3sportsbet_models_betset::getBetsetInstance($this->getProperty('betset'));
    }

    /**
     * Goals home.
     *
     * @return int
     */
    public function getGoalsHome()
    {
        return intval($this->getProperty('goals_home'));
    }

    /**
     * Goals guest.
     *
     * @return int
     */
    public function getGoalsGuest()
    {
        return intval($this->getProperty('goals_guest'));
    }

    public function getToto()
    {
        $goalsHome = $this->getGoalsHome();
        $goalsGuest = $this->getGoalsGuest();
        $goalsDiff = $goalsHome - $goalsGuest;
        if (0 == $goalsDiff) {
            return 0;
        }

        return ($goalsDiff < 0) ? 2 : 1;
    }
}
