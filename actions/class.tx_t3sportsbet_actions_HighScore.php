<?php

use Sys25\RnBase\Domain\Repository\FeUserRepository;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Utility\PageBrowser;
use Sys25\T3sportsbet\Utility\ServiceRegistry;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2019 Rene Nitzsche (rene@system25.de)
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
 * Der View zeigt die Bestenliste an.
 */
class tx_t3sportsbet_actions_HighScore extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    private $feuserRepo;

    public function __construct()
    {
        $this->feuserRepo = new FeUserRepository();
    }

    /**
     * @param RequestInterface $request
     *
     * @return string error msg or null
     */
    protected function handleRequest(RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $configurations = $request->getConfigurations();
        // Mit den Betsets kann man Zwischenauswertungen machen
        $scopeArr = tx_t3sportsbet_util_ScopeController::handleCurrentScope($request, []);
        $betgames = tx_t3sportsbet_util_ScopeController::getBetgamesFromScope($scopeArr['BETGAME_UIDS']);
        $betsetUids = $scopeArr['BETSET_UIDS'];
        // Um etwas zu zeigen, benötigen wir Betset-Ids
        if (!$betsetUids) {
            return $configurations->getLL('error_nobetsets_defined');
        }
        // Liste von Nutzern
        // Sortiert nach den Punkten in den Tips
        // 1. Abfrage: Anzahl der Nutzer mit Tips
        $betSrv = ServiceRegistry::getBetService();
        // $listSize = $betSrv->getResultSize($betsetUids);
        // Die gesamten Daten holen
        $feuser = $this->feuserRepo->getCurrent();
        $userUids = ($feuser) ? $feuser->getUid() : '';
        $results = $betSrv->getResults($betsetUids, $userUids);
        $listSize = count($results[0]);

        $pageBrowser = tx_rnbase::makeInstance(PageBrowser::class, 'bethighscores'.$configurations->getCObj()->data['uid']);
        $pageSize = $this->getPageSize($parameters, $configurations);
        $pageBrowser->setState($parameters, $listSize, $pageSize);
        $limit = $pageBrowser->getState();
        // Aus der Gesamtliste den gesuchten Abschnitt herausschneiden
        $userPoints = array_slice($results[0], $limit['offset'], $limit['limit']);
        $currUserPoints = ($feuser) ? $results[0][$results[1][$feuser->getUid()]] : [];

        $viewData = $request->getViewContext();
        $viewData->offsetSet('betgame', $betgames[0]);
        $viewData->offsetSet('userPoints', $userPoints);
        $viewData->offsetSet('currUserPoints', $currUserPoints);
        $viewData->offsetSet('userSize', $pageBrowser->getListSize());
        $viewData->offsetSet('pagebrowser', $pageBrowser);

        return null;
    }

    /**
     * Liefert die Anzahl der Ergebnisse pro Seite.
     *
     * @param \Sys25\RnBase\Frontend\Request\ParametersInterface $parameters
     * @param \Sys25\RnBase\Configuration\ConfigurationInterface $configurations
     *
     * @return int
     */
    protected function getPageSize($parameters, $configurations)
    {
        return $configurations->getInt('highscore.feuser.pagebrowser.limit');
    }

    protected function getTemplateName()
    {
        return 'highscore';
    }

    protected function getViewClassName()
    {
        return 'tx_t3sportsbet_views_HighScore';
    }
}
