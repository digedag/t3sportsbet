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
tx_rnbase::load('Tx_Rnbase_Utility_Strings');

/**
 * Auswahl des Scopes im FE bereitstellen.
 */
class tx_t3sportsbet_util_ScopeController
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
    public static function handleCurrentScope(\Sys25\RnBase\Frontend\Request\RequestInterface $request, $options = [])
    {
        $ret = [];
        $ret['BETGAME_UIDS'] = self::handleCurrentBetgame($request, $options);
        $ret['BETSET_UIDS'] = self::handleCurrentBetset($request, $options);

        return $ret;
    }

    protected static function handleCurrentBetgame(\Sys25\RnBase\Frontend\Request\RequestInterface $request, &$options)
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
     * @return array[tx_t3sportsbet_models_betset] betsets to show
     */
    public static function handleCurrentBetset(\Sys25\RnBase\Frontend\Request\RequestInterface $request, $options)
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
        tx_rnbase::load('tx_rnbase_util_Misc');
        $ret = tx_rnbase_util_Misc::objImplode(',', $rounds);

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
        $fields = array();
        $options = array();
        $options['distinct'] = 1;
        tx_rnbase::load('tx_rnbase_util_SearchBase');
        tx_rnbase_util_SearchBase::setConfigFields($fields, $configurations, $confId.'fields.');
        tx_rnbase_util_SearchBase::setConfigOptions($options, $configurations, $confId.'options.');
        $srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
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
        $uids = Tx_Rnbase_Utility_Strings::intExplode(',', $uids);
        $rounds = array();
        tx_rnbase::load('tx_t3sportsbet_models_betgame');
        for ($i = 0, $cnt = count($uids); $i < $cnt; ++$i ) {
            $rounds[] = tx_t3sportsbet_models_betgame::getBetgameInstance($uids[$i]);
        }

        return $rounds;
    }

    public static function getRoundsFromScope($uids)
    {
        $uids = Tx_Rnbase_Utility_Strings::intExplode(',', $uids);
        $rounds = array();
        tx_rnbase::load('tx_t3sportsbet_models_betset');
        for ($i = 0, $cnt = count($uids); $i < $cnt; ++$i ) {
            $rounds[] = tx_t3sportsbet_models_betset::getBetsetInstance($uids[$i]);
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
    protected static function _prepareSelect($objects, $parameters, $parameterName, $displayAttrName = 'name', $defaultIdx = 0)
    {
        $ret = [];
        if (count($objects)) {
            foreach ($objects as $object) {
                $ret[0][$object->getUid()] = 0 == strlen($displayAttrName) ? $object : $object->getProperty($displayAttrName);
            }

            $paramValue = $parameters->offsetGet($parameterName);
            // Der Wert im Parameter darf nur übernommen werden, wenn er in der SelectBox vorkommt
            if (isset($paramValue) && array_key_exists($paramValue, $ret[0])) {
                $ret[1] = $paramValue;
            }
            $ret[1] = $ret[1] ? $ret[1] : $objects[$defaultIdx]->getUid();
        }

        return $ret;
    }
}
