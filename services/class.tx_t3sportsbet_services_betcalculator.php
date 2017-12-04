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
tx_rnbase::load('Tx_Rnbase_Service_Base');

/**
 * This service calculates the points for a bet
 *
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_services_betcalculator extends Tx_Rnbase_Service_Base
{

    /**
     *
     * @param tx_t3sportsbet_models_betgame $betgame            
     * @param tx_cfcleaguefe_models_match $match            
     */
    public function getGoals($betgame, $match)
    {
        $mpart = '';
        // last bedeutet nach Ende der regulären Spielzeit
        $mpart = ($match->isExtraTime() && $betgame->isDrawIfExtraTime()) ? 'last' : $mpart;
        $mpart = ($match->isPenalty() && $betgame->isDrawIfPenalty()) ? 'et' : $mpart;
        $goalsHome = $match->getGoalsHome($mpart);
        $goalsGuest = $match->getGoalsGuest($mpart);
        return array(
            $goalsHome,
            $goalsGuest
        );
    }

    /**
     * Calculates the points for a bet
     *
     * @param tx_t3sportsbet_models_betgame $betGame            
     * @param tx_t3sportsbet_models_bet $bet            
     */
    public function calculatePoints($betgame, $bet)
    {
        $match = $bet->getMatch();
        
        // TODO: GreenTable kann noch nicht ermittelt werden...
        // 1. Schritt: Spielergebnis ermitteln
        list ($goalsHome, $goalsGuest) = $this->getGoals($betgame, $match);
        $ret = 0;
        // Daten für Tordifferenz vorbereiten
        $diffMatch = $goalsHome - $goalsGuest;
        $diffBet = $bet->getGoalsHome() - $bet->getGoalsGuest();
        // Auswertung nach
        // Genauer Tip
        if ($bet->getGoalsHome() == $goalsHome && $bet->getGoalsGuest() == $goalsGuest) {
            $ret = $betgame->getPointsAccurate();
        } elseif ($diffMatch == $diffBet && $betgame->getPointsGoalsDiff()) {
            $ret = $betgame->getPointsGoalsDiff();
        } elseif ($bet->getToto() == $this->getToto($goalsHome, $goalsGuest)) {
            // Tendency
            $ret = $betgame->getPointsTendency();
        }
        return $ret;
    }

    public function getToto($goalsHome, $goalsGuest)
    {
        $goalsDiff = $goalsHome - $goalsGuest;
        if ($goalsDiff == 0) {
            return 0;
        }
        return ($goalsDiff < 0) ? 2 : 1;
    }
}
