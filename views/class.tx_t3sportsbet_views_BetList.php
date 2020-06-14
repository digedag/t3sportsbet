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
tx_rnbase::load('tx_t3sportsbet_util_FeUserMarker');

/**
 * Viewklasse für die Darstellung von Tipplisten.
 */
class tx_t3sportsbet_views_BetList extends \Sys25\RnBase\Frontend\View\Marker\BaseView
{
    /**
     * Erstellt die Ausgabe für die Liste der Tiprunden.
     *
     * @param string $template
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     * @param tx_rnbase_util_FormatUtil $formatter
     *
     * @return string
     */
    protected function createOutput($template, Sys25\RnBase\Frontend\Request\RequestInterface $request, $formatter)
    {
        $viewData = $request->getViewContext();
        // Wir holen die Daten von der Action ab
        $betgame = $viewData->offsetGet('betgame');
        $feuser = $viewData->offsetGet('feuser');
        $currFeuser = $viewData->offsetGet('currfeuser');

        $params = [];
        $params['confid'] = 'betlist.';
        $params['betgame'] = $betgame;
        $params['feuser'] = $feuser;
        $markerArray = $subpartArray = $wrappedSubpartArray = $data = [];
        $subpartArray['###BETSET_SELECTIONS###'] = '';
        if ($viewData->offsetExists('saved')) {
            $wrappedSubpartArray['###BETSET_SAVED###'] = [
                '',
                '',
            ];
            $data['savecount'] = $viewData->offsetGet('saved');
        } else {
            $subpartArray['###BETSET_SAVED###'] = '';
        }

        // Wenn Selectbox für Tiprunde gezeigt werden soll, dann Abschnitt erstellen
        $selectItems = $viewData->offsetGet('betset_select');
        $selectItems = is_array($selectItems) ? $selectItems : [];
        $template = $this->addScope($template, $viewData, $selectItems, 'betlist.betset.', 'BETSET', $formatter);

        if (is_object($currFeuser)) {
            $subpartArray['###LOGINMESSAGE###'] = '';
            $wrappedSubpartArray['###IS_LOGGEDIN###'] = ['', ''];
            $subpartArray['###IS_LOGGEDOUT###'] = '';
        } else {
            $wrappedSubpartArray['###LOGINMESSAGE###'] = ['', ''];
            $subpartArray['###IS_LOGGEDIN###'] = '';
            $wrappedSubpartArray['###IS_LOGGEDOUT###'] = ['', ''];
        }

        $betsets = $viewData->offsetGet('rounds');
        if (count($betsets)) {
            $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
            $template = $listBuilder->render($betsets, $viewData, $template, 'tx_t3sportsbet_util_BetSetMarker', 'betlist.betset.', 'BETSET', $formatter, $params);
            // $markerArray['###ACTION_URI###'] = $this->createPageUri($configurations);
            $data['ACTION_URI'] = $this->createPageUri($request);
            // Siehe tx_t3sportsbet_util_MatchMarker: Das ist nur ein Hack! Der Status sollte besser übergeben werden!
            $matchState = $request->getConfigurations()->getViewData()->offsetGet('MATCH_STATE');
            if ('OPEN' == $matchState) {
                $wrappedSubpartArray['###SAVEBUTTON###'] = [
                    '',
                    '',
                ];
            } else {
                $subpartArray['###SAVEBUTTON###'] = '';
            }
        } else {
            $subpartArray['###BETSETS###'] = $request->getConfigurations()->getLL('msg_no_betsets_found');
        }

        $userMarker = tx_rnbase::makeInstance('tx_t3sportsbet_util_FeUserMarker');
        if ($feuser) {
            $template = $userMarker->parseTemplate($template, $feuser, $formatter, 'betlist.feuser.', 'FEUSER');
        }

        $markerArray = $formatter->getItemMarkerArrayWrapped($data, 'betlist.');
        tx_rnbase_util_BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    /**
     * @param tx_rnbase_configurations $configurations
     */
    protected function createPageUri(Sys25\RnBase\Frontend\Request\RequestInterface $request, $params = [])
    {
        $configurations = $request->getConfigurations();
        $link = $configurations->createLink();
        $link->initByTS($configurations, $request->getConfId().'formUrl.', $params);
        if ($configurations->get($request->getConfId().'formUrl.noCache')) {
            $link->noCache();
        }

        return $link->makeUrl(false);
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
        $template = $listBuilder->render($betsets, $viewData, $template, 'tx_t3sportsbet_util_BetSetMarker', $confId.'selection.', $markerName.'_SELECTION', $formatter, array(
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
        return '###BETLIST###';
    }
}
