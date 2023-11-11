<?php

namespace Sys25\T3sportsbet\Frontend\View;

use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\ListBuilder;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\ContextInterface;
use Sys25\T3sportsbet\Frontend\Marker\BetSetMarker;
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
 * Viewklasse für die Darstellung der Bestenliste.
 */
class ScopeSelectionView extends \Sys25\RnBase\Frontend\View\Marker\BaseView
{
    /**
     * @param string $template
     * @param RequestInterface $request
     * @param tx_rnbase_util_FormatUtil $formatter
     *
     * @return string
     */
    protected function createOutput($template, RequestInterface $request, $formatter)
    {
        $viewData = $request->getViewContext();
        // Wenn Selectbox für Tiprunde gezeigt werden soll, dann Abschnitt erstellen
        $selectItems = $viewData->offsetExists('betset_select') ? $viewData->offsetGet('betset_select') : [];
        $selectItems = is_array($selectItems) ? $selectItems : [];
        $template = $this->addScope($template, $viewData, $selectItems, 'scope.betset.', 'BETSET', $formatter);
        $params = ['confid' => $request->getConfId()];
        $markerArray = $subpartArray = $wrappedSubpartArray = [];

        BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    private function addScope($template, $viewData, $itemsArr, $confId, $markerName, $formatter)
    {
        $currItem = '';
        if (!empty($itemsArr)) {
            $betsets = $itemsArr[0];
            $currItem = $betsets[$itemsArr[1]];
            // Die Betsets liegen in einem Hash, sie müssen aber in ein einfaches Array
            $betsets = array_values($betsets);
        }
        $listBuilder = tx_rnbase::makeInstance(ListBuilder::class);
        $template = $listBuilder->render($betsets, $viewData, $template, BetSetMarker::class, $confId, $markerName, $formatter, [
            'currItem' => $currItem,
        ]);

        return $template;
    }

    /**
     * Subpart der im HTML-Template geladen werden soll.
     * Dieser wird der Methode
     * createOutput automatisch als $template übergeben.
     *
     * @return string
     */
    protected function getMainSubpart(ContextInterface $viewData)
    {
        return '###SCOPE###';
    }
}
