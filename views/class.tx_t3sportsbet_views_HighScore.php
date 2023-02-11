<?php

use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Domain\Repository\FeUserRepository;
use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\ListBuilder;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\ContextInterface;
use Sys25\RnBase\Frontend\View\Marker\BaseView;

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
class tx_t3sportsbet_views_HighScore extends BaseView
{
    private $feuserRepo;

    public function __construct()
    {
        $this->feuserRepo = new FeUserRepository();
    }

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
        // Wir holen die Daten von der Action ab
        $betgame = $viewData->offsetGet('betgame');
        $userPoints = $viewData->offsetGet('userPoints');
        $currUserPoints = $viewData->offsetGet('currUserPoints');
        $userSize = $viewData->offsetGet('userSize');

        // Wenn Selectbox für Tiprunde gezeigt werden soll, dann Abschnitt erstellen
        $selectItems = $viewData->offsetGet('betset_select');
        $selectItems = is_array($selectItems) ? $selectItems : [];
        $template = $this->addScope($template, $viewData, $selectItems, 'highscore.betset.', 'BETSET', $formatter);

        // Wir haben jetzt erstmal nur die UIDs und die Punktezahl. Die Nutzerdaten müssen erst geladen werden
        $users = $this->getUsers($userPoints, $userSize);
        $listBuilder = tx_rnbase::makeInstance(ListBuilder::class);
        $template = $listBuilder->render($users, $viewData, $template, 'tx_t3sportsbet_util_FeUserMarker', 'highscore.feuser.', 'FEUSER', $formatter);

        // Anzeige des aktuellen Users
        $markerArray = $subpartArray = $wrappedSubpartArray = [];
        $subpartArray['###CURRUSER###'] = $this->_addCurrUser($currUserPoints, Templates::getSubpart($template, '###CURRUSER###'), $formatter, 'currUser.', 'CURRUSER');
        $template = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        $params = [];
        $params['confid'] = $request->getConfId();
        $params['betgame'] = $betgame;
        $markerArray = $subpartArray = $wrappedSubpartArray = [];

        BaseMarker::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    protected function _addCurrUser($currUserPoints, $template, &$formatter, $confId, $marker)
    {
        if (!isset($currUserPoints['uid']) || !$currUserPoints['uid']) {
            return '';
        }
        $feuser = $this->feuserRepo->getInstance($currUserPoints['uid']);
        if (!$feuser->isValid()) {
            return '';
        }
        $this->setAddUserData($feuser, $currUserPoints);

        $subpartArray = $wrappedSubpartArray = [];
        $markerArray = $formatter->getItemMarkerArrayWrapped($feuser->getProperty(), $confId, 0, $marker.'_', $feuser->getColumnNames());
        $template = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $template;
    }

    /**
     * Return feuser objects for point list.
     *
     * @param array $userPoints
     *
     * @return array
     */
    protected function getUsers($userPoints, $userSize)
    {
        $users = [];
        for ($i = 0, $cnt = count($userPoints); $i < $cnt; ++$i) {
            // Wenn hier ein User gelöscht wurde, dann... :-(
            $feuser = $this->feuserRepo->getInstance($userPoints[$i]['uid']);
            $this->setAddUserData($feuser, $userPoints[$i]);
            $users[] = $feuser;
        }

        return $users;
    }

    /**
     * @param FeUser $feuser
     * @param array $data
     */
    protected function setAddUserData($feuser, $data)
    {
        $feuser->setProperty('betpoints', $data['betpoints']);
        $feuser->setProperty('betrank', $data['rank']);
        $feuser->setProperty('betmark', $data['mark']);
        $feuser->setProperty('betcount', $data['betcount']);
        $feuser->setProperty('avgpoints', $data['betcount'] ? round($data['betpoints'] / $data['betcount'], 1) : 0);
    }

    private function addScope($template, $viewData, $itemsArr, $confId, $markerName, $formatter)
    {
        if (!empty($itemsArr)) {
            $betsets = $itemsArr[0];
            $currItem = $betsets[$itemsArr[1]];
            // Die Betsets liegen in einem Hash, sie müssen aber in ein einfaches Array
            $betsets = array_values($betsets);
        }
        $listBuilder = tx_rnbase::makeInstance(ListBuilder::class);
        $template = $listBuilder->render($betsets, $viewData, $template, 'tx_t3sportsbet_util_BetSetMarker', $confId.'selection.', $markerName.'_SELECTION', $formatter, [
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
        return '###HIGHSCORE###';
    }
}
