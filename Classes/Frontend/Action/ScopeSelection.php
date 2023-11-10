<?php

namespace Sys25\T3sportsbet\Frontend\Action;

use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\T3sportsbet\Frontend\View\ScopeSelectionView;
use tx_t3sportsbet_util_ScopeController as ScopeController;

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
 * Der View zeigt Auswahlbox für Tiprunden an und speichert Veränderungen.
 */
class ScopeSelection extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    /**
     * @param RequestInterface $request
     *
     * @return string error msg or null
     */
    protected function handleRequest(RequestInterface $request)
    {
        // Wir zeigen entweder die offenen oder die schon fertigen Tipps
        // Dies wird per Config festgelegt
        $options = [];
        $options['betsetkey'] = 'scope.';
        $scopeArr = ScopeController::handleCurrentScope($request, $options);
        $betgames = ScopeController::getBetgamesFromScope($scopeArr['BETGAME_UIDS']);
        $rounds = ScopeController::getRoundsFromScope($scopeArr['BETSET_UIDS']);

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
        return ScopeSelectionView::class;
    }
}
