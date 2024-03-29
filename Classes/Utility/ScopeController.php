<?php

namespace Sys25\T3sportsbet\Utility;

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

use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\T3sportsbet\Model\BetGame;
use Sys25\T3sportsbet\Model\BetSet;

/**
 * Auswahl des Scopes im FE bereitstellen.
 */
class ScopeController
{
    // Speichert die UID des aktuellen cObject
    private static $_cObjectUID = [];

    /**
     * Diese Funktion stellt die UIDs der aktuell ausgewählten Ligen bereit.
     * Durch den Aufruf werden gleichzeitig die Daten für die Select-Boxen
     * vorbereitet und in die viewData der Config gelegt.
     * Es wird ein Array mit dem aktuell gültigen Scope zurückgeliefert.
     *
     * @return array mit den UIDs als String
     */
    public static function handleCurrentScope(RequestInterface $request, $options = [])
    {
        $ret = [];
        $ret['BETGAME_UIDS'] = self::handleCurrentBetgame($request, $options);
        $ret['BETSET_UIDS'] = self::handleCurrentBetset($request, $options);

        return $ret;
    }

    protected static function handleCurrentBetgame(RequestInterface $request, array &$options)
    {
        // Erstmal nur ein Tipspiel im Scope erlaubt
        $configurations = $request->getConfigurations();
        $betgameUid = $configurations->get('scope.betgame');
        $options['betgame'] = $betgameUid;

        return $betgameUid;
    }

    /**
     * Diese Funktion stellt die UIDs der aktuell ausgewählten Betsets bereit.
     * Durch den Aufruf werden gleichzeitig die Daten für die Select-Boxen
     * vorbereitet und in die viewData der Config gelegt.
     *
     * @return BetSet[] betsets to show
     */
    public static function handleCurrentBetset(RequestInterface $request, array $options)
    {
        $configurations = $request->getConfigurations();
        $parameters = $request->getParameters();
        $betgame = $options['betgame'];
        $useObjects = true;
        $configKey = isset($options['betsetkey']) ? $options['betsetkey'] : 'scope.';
        $viewData = $request->getViewContext();
        $betsetUids = $configurations->get($configKey.'betset');
        $betsetStatus = $configurations->get($configKey.'betsetStatus');
        $rounds = self::getBetsets($betgame, $betsetStatus, $betsetUids, $configurations, $configKey);
        $ret = Misc::objImplode(',', $rounds);

        // Soll eine SelectBox für die Tiprunde gezeigt werden?
        if ($configurations->get($configKey.'betsetInput')) {
            $defaultBetset = $configurations->get($configKey.'defaultBetset');
            $defaultIdx = 'first' == $defaultBetset ? 0 : count($rounds) - 1;
            // Die UIDs der Saisons in Objekte umwandeln um eine Selectbox zu bauen
            $dataArr = self::_prepareSelect($rounds, $parameters, 'betset', $useObjects ? '' : 'round_name', $defaultIdx);
            $ret = $dataArr[1];
            // $ret = $dataArr[0][$dataArr[1]]; // Das Objekt laden
            $viewData->offsetSet('betset_select', $dataArr);
            // $configurations->addKeepVar('betset',$betsetUids);
        }

        return $ret;
    }

    private static function getBetsets($betgameUid, $betsetStatus, $betsetUids, &$configurations, $confId = 'scope.')
    {
        $fields = [];
        $options = [];
        $options['distinct'] = 1;

        SearchBase::setConfigFields($fields, $configurations, $confId.'fields.');
        SearchBase::setConfigOptions($options, $configurations, $confId.'options.');
        $srv = ServiceRegistry::getBetService();
        if (strlen(trim($betgameUid))) {
            $fields['BETSET.BETGAME'][OP_IN_INT] = $betgameUid;
        }
        if (trim($betsetStatus)) {
            $fields['BETSET.STATUS'][OP_IN_INT] = $betsetStatus;
        }
        if (trim($betsetUids)) {
            $fields['BETSET.UID'][OP_IN_INT] = $betsetUids;
        }

        return $srv->searchBetSet($fields, $options);
    }

    public static function getBetgamesFromScope($uids)
    {
        $uids = $uids ? Strings::intExplode(',', $uids) : [];
        $rounds = [];
        for ($i = 0, $cnt = count($uids); $i < $cnt; ++$i) {
            $rounds[] = BetGame::getBetgameInstance($uids[$i]);
        }

        return $rounds;
    }

    public static function getRoundsFromScope($uids)
    {
        $uids = $uids ? Strings::intExplode(',', $uids) : [];
        $rounds = [];
        for ($i = 0, $cnt = count($uids); $i < $cnt; ++$i) {
            $rounds[] = BetSet::getBetsetInstance($uids[$i]);
        }

        return $rounds;
    }

    /**
     * Liefert ein Array für die Erstellung der Select-Box für eine Model-Klasse
     * Das Ergebnis-Array hat zwei Einträge: Index 0 enthält das Wertearray, Index 1 das
     * aktuelle Element.
     *
     * @param string $displayAttrName Der Name eines Atttributs, um dessen Wert anzuzeigen. Wenn der
     *            String leer ist, dann wird das gesamten Objekt als Wert verwendet.
     * @param int $defaultIdx
     */
    protected static function _prepareSelect($objects, Parameters $parameters, $parameterName, $displayAttrName = 'name', $defaultIdx = 0)
    {
        $ret = [];
        if (count($objects)) {
            foreach ($objects as $object) {
                $ret[0][$object->getUid()] = 0 == strlen($displayAttrName) ? $object : $object->getProperty($displayAttrName);
            }

            $paramValue = $parameters->get($parameterName);
            // Der Wert im Parameter darf nur übernommen werden, wenn er in der SelectBox vorkommt
            if (isset($paramValue) && array_key_exists($paramValue, $ret[0])) {
                $ret[1] = $paramValue;
            }
            $ret[1] = $ret[1] ?? $objects[$defaultIdx]->getUid();
        }

        return $ret;
    }
}
