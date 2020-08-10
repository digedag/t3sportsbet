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
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Keine echte Registry, aber eine zentrale Klasse für den Zugriff auf verschiedene
 * Services.
 */
class tx_t3sportsbet_util_serviceRegistry
{
    /**
     * Returns the bet service.
     *
     * @return tx_t3sportsbet_services_betcalculator
     */
    public static function getCalculatorService()
    {
        return tx_rnbase_util_Misc::getService('t3sportsbet', 'calculator');
    }

    /**
     * Returns the teambet service.
     *
     * @return tx_t3sportsbet_services_teambet
     */
    public static function getTeamBetService()
    {
        return tx_rnbase_util_Misc::getService('t3sportsbet', 'teambet');
    }

    /**
     * Returns the bet service.
     *
     * @return tx_t3sportsbet_services_bet
     */
    public static function getBetService()
    {
        return tx_rnbase_util_Misc::getService('t3sportsbet', 'bet');
    }

    /**
     * Returns the available data providers for matches.
     *
     * @return array
     */
    public function lookupDataProvider($config)
    {
        $services = tx_rnbase_util_Misc::lookupServices('t3sportsbet_dataprovider');
        foreach ($services as $subtype => $info) {
            $title = $info['title'];
            if ('LLL:' === substr($title, 0, 4)) {
                $title = $GLOBALS['LANG']->sL($title);
            }
            $config['items'][] = [
                $title,
                $subtype,
            ];
        }

        return $config;
    }
}
