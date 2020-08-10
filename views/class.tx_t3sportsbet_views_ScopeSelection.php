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
tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_ListBuilder');

/**
 * Viewklasse für die Darstellung der Bestenliste.
 */
class tx_t3sportsbet_views_ScopeSelection extends \Sys25\RnBase\Frontend\View\Marker\BaseView
{
    /**
     * @param string $template
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     * @param tx_rnbase_util_FormatUtil $formatter
     *
     * @return string
     */
    protected function createOutput($template, Sys25\RnBase\Frontend\Request\RequestInterface $request, $formatter)
    {
        $viewData = $request->getViewContext();
        // Wenn Selectbox für Tiprunde gezeigt werden soll, dann Abschnitt erstellen
        $selectItems = $viewData->offsetGet('betset_select');
        $selectItems = is_array($selectItems) ? $selectItems : [];
        $template = $this->addScope($template, $viewData, $selectItems, 'scope.betset.', 'BETSET', $formatter);
        $params = ['confid' => $request->getConfId()];
        $markerArray = $subpartArray = $wrappedSubpartArray = [];

        tx_rnbase_util_BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    private function addScope($template, $viewData, $itemsArr, $confId, $markerName, $formatter)
    {
        if (!empty($itemsArr)) {
            $betsets = $itemsArr[0];
            $currItem = $betsets[$itemsArr[1]];
            // Die Betsets liegen in einem Hash, sie müssen aber in ein einfaches Array
            $betsets = array_values($betsets);
        }
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $template = $listBuilder->render($betsets, $viewData, $template, 'tx_t3sportsbet_util_BetSetMarker', $confId, $markerName, $formatter, array(
            'currItem' => $currItem,
        ));

        return $template;
    }

    /**
     * Subpart der im HTML-Template geladen werden soll.
     * Dieser wird der Methode
     * createOutput automatisch als $template übergeben.
     *
     * @return string
     */
    protected function getMainSubpart(Sys25\RnBase\Frontend\View\ContextInterface $viewData)
    {
        return '###SCOPE###';
    }
}
