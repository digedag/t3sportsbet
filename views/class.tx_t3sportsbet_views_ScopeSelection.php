<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2018 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_view_Base');
tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_ListBuilder');

/**
 * Viewklasse für die Darstellung der Bestenliste
 */
class tx_t3sportsbet_views_ScopeSelection extends tx_rnbase_view_Base
{

    function createOutput($template, &$viewData, &$configurations, &$formatter)
    {
        // Wenn Selectbox für Tiprunde gezeigt werden soll, dann Abschnitt erstellen
        $selectItems = $viewData->offsetGet('betset_select');
        $selectItems = is_array($selectItems) ? $selectItems : array();
        $template = $this->addScope($template, $viewData, $selectItems, 'scope.betset.', 'BETSET', $formatter);
        $params['confid'] = 'scope.';
        $markerArray = array();
        $subpartArray = array();
        $wrappedSubpartArray = array();

        tx_rnbase_util_BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = $formatter->cObj->substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
        return $out;
    }

    private function addScope($template, &$viewData, &$itemsArr, $confId, $markerName, &$formatter)
    {
        if (count($itemsArr)) {
            $betsets = $itemsArr[0];
            $currItem = $betsets[$itemsArr[1]];
            // Die Betsets liegen in einem Hash, sie müssen aber in ein einfaches Array
            $betsets = array_values($betsets);
        }
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $template = $listBuilder->render($betsets, $viewData, $template, 'tx_t3sportsbet_util_BetSetMarker', $confId, $markerName, $formatter, array(
            'currItem' => $currItem
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
    function getMainSubpart()
    {
        return '###SCOPE###';
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/views/class.tx_t3sportsbet_views_ScopeSelection.php']) {
    include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/views/class.tx_t3sportsbet_views_ScopeSelection.php']);
}
?>