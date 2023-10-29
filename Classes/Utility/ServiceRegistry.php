<?php

namespace Sys25\T3sportsbet\Utility;

use Sys25\RnBase\Utility\Misc;
use Sys25\T3sportsbet\Service\BetCalculator;
use Sys25\T3sportsbet\Service\BetService;
use Sys25\T3sportsbet\Service\TeamBetService;
use tx_rnbase;

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
 * Keine echte Registry, aber eine zentrale Klasse für den Zugriff auf verschiedene
 * Services.
 */
class ServiceRegistry implements \TYPO3\CMS\Core\SingletonInterface
{
    private $betService;
    private $betCalculator;
    private $teamBetService;

    public function __construct(
        ?BetService $betService = null,
        ?BetCalculator $betCalculator = null,
        ?TeamBetService $teamBetService = null
    ) {
        $this->betService = $betService ?? tx_rnbase::makeInstance(BetService::class);
        $this->betCalculator = $betCalculator ?? tx_rnbase::makeInstance(BetCalculator::class);;
        $this->teamBetService = $teamBetService ?? tx_rnbase::makeInstance(TeamBetService::class);;
    }

    /**
     * @return self
     */
    private static function getInstance()
    {
        return tx_rnbase::makeInstance(ServiceRegistry::class);
    }

    /**
     * Returns the bet service.
     *
     * @return tx_t3sportsbet_services_betcalculator
     */
    public static function getCalculatorService()
    {
        return self::getInstance()->betCalculator;
    }

    /**
     * Returns the teambet service.
     *
     * @return tx_t3sportsbet_services_teambet
     */
    public static function getTeamBetService()
    {
        return self::getInstance()->teamBetService;
    }

    /**
     * Returns the bet service.
     *
     * @return BetService
     */
    public static function getBetService()
    {
        return self::getInstance()->betService;
    }

    /**
     * Returns the available data providers for matches.
     *
     * @return array
     */
    public function lookupDataProvider($config)
    {
        $services = Misc::lookupServices('t3sportsbet_dataprovider');
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
