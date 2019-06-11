<?php
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
tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3sportsbet_models_betgame');
tx_rnbase::load('tx_t3users_models_feuser');
tx_rnbase::load('tx_t3sportsbet_util_ScopeController');

/**
 * Der View zeigt Auswahlbox für Tiprunden an und speichert Veränderungen.
 */
class tx_t3sportsbet_actions_ScopeSelection extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{

    /**
     *
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     * @return string error msg or null
     */
    protected function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        // Wir zeigen entweder die offenen oder die schon fertigen Tipps
        // Dies wird per Config festgelegt
        $options = [];
        $options['betsetkey'] = 'scope.';
        $scopeArr = tx_t3sportsbet_util_ScopeController::handleCurrentScope($request, $options);
        $betgames = tx_t3sportsbet_util_ScopeController::getBetgamesFromScope($scopeArr['BETGAME_UIDS']);
        $rounds = tx_t3sportsbet_util_ScopeController::getRoundsFromScope($scopeArr['BETSET_UIDS']);

        $viewData = $request->getViewContext();
        $viewData->offsetSet('betgame', $betgames[0]);
        $viewData->offsetSet('rounds', $rounds);

        // Wenn wir hier direkt etwas zurückgeben, wird der View nicht
        // aufgerufen. Eher für Abbruch im Fehlerfall gedacht.
        return null;
    }

    protected function getTemplateName()
    {
        return 'scope';
    }

    protected function getViewClassName()
    {
        return 'tx_t3sportsbet_views_ScopeSelection';
    }
}
