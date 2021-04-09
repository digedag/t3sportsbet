<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2017 Rene Nitzsche (rene@system25.de)
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

/**
 * Diese Klasse ist für die Erstellung von Markerarrays der Tipprunden verantwortlich.
 */
class tx_t3sportsbet_util_BetSetMarker extends tx_rnbase_util_BaseMarker
{
    public function __construct($options = array())
    {
        $this->options = $options;
    }

    /**
     * @param string $template
     *            das HTML-Template
     * @param tx_t3sportsbet_models_betset $betset
     *            die Tipprunde
     * @param tx_rnbase_util_FormatUtil $formatter
     *            der zu verwendente Formatter
     * @param string $confId
     *            Pfad der TS-Config des Vereins, z.B. 'listView.round.'
     * @param string $marker
     *            Name des Markers für die Tipprunde, z.B. ROUND
     *
     * @return string das geparste Template
     */
    public function parseTemplate($template, &$betset, &$formatter, $confId, $marker = 'BETSET')
    {
        if (!is_object($betset)) {
            $betset = self::getEmptyInstance('tx_t3sportsbet_models_betset');
        }
        $currItem = isset($this->options['currItem']) ? $this->options['currItem'] : false;
        $betset->setProperty('isCurrent', $currItem && $currItem->uid == $betset->uid);
        // Die Spiele einbinden.
        if ($this->containsMarker($template, $marker.'_MATCHS')) {
            $template = $this->_addMatches($template, $betset, $formatter, $confId.'match.', $marker.'_MATCH');
        }
        if ($this->containsMarker($template, $marker.'_TEAMBETS')) {
            $template = $this->_addTeamBets($template, $betset, $formatter, $confId.'teambet.', $marker.'_TEAMBET');
        }

        $markerArray = $formatter->getItemMarkerArrayWrapped($betset->record, $confId, 0, $marker.'_', $betset->getColumnNames());
        $subpartArray = array();
        $wrappedSubpartArray = array();
        $this->prepareLinks($betset, $marker, $markerArray, $subpartArray, $wrappedSubpartArray, $confId, $formatter);
        $template = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        $markerArray = array();
        $subpartArray = array();
        $wrappedSubpartArray = array();

        $params['confid'] = $confId;
        $params['marker'] = $marker;
        $params['betset'] = $betset;
        self::callModules($template, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $formatter);
        $out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    /**
     * Add matches of betset.
     *
     * @param tx_t3sportsbet_models_betset $betset
     * @param string $template
     * @param tx_rnbase_util_FormatUtil $formatter
     * @param string $confId
     * @param string $marker
     *
     * @return string
     */
    private function _addTeamBets($template, &$betset, &$formatter, $confId, $marker)
    {
        $srv = tx_t3sportsbet_util_serviceRegistry::getTeamBetService();
        $fields['TEAMQUESTION.BETSET'][OP_EQ_INT] = $betset->getUid();
        $options = array();
        tx_rnbase_util_SearchBase::setConfigFields($fields, $formatter->configurations, $confId.'fields.');
        tx_rnbase_util_SearchBase::setConfigOptions($options, $formatter->configurations, $confId.'options.');
        $children = $srv->searchTeamQuestion($fields, $options);
        $markerParams = $this->options;
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $out = $listBuilder->render($children, false, $template, 'tx_t3sportsbet_util_TeamQuestionMarker', $confId, $marker, $formatter, $markerParams);

        return $out;
    }

    /**
     * Add matches of betset.
     *
     * @param tx_t3sportsbet_models_betset $betset
     * @param string $template
     * @param tx_rnbase_util_FormatUtil $formatter
     * @param string $confId
     * @param string $marker
     *
     * @return string
     */
    private function _addMatches($template, $betset, $formatter, $confId, $marker)
    {
        $srv = tx_cfcleaguefe_util_ServiceRegistry::getMatchService();
        $fields['BETSETMM.UID_LOCAL'][OP_EQ_INT] = $betset->getUid();
        $options = array();
        tx_rnbase_util_SearchBase::setConfigFields($fields, $formatter->configurations, $confId.'fields.');
        tx_rnbase_util_SearchBase::setConfigOptions($options, $formatter->configurations, $confId.'options.');
        $children = $srv->search($fields, $options);
        $markerParams = $this->options;
        $markerParams['betset'] = $betset;

        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $out = $listBuilder->render($children, false, $template, 'tx_t3sportsbet_util_MatchMarker', $confId, $marker, $formatter, $markerParams);

        return $out;
    }

    /**
     * Links vorbereiten.
     *
     * @param tx_t3sportsbet_models_betset $betset
     * @param string $marker
     * @param array $markerArray
     * @param array $wrappedSubpartArray
     * @param string $confId
     * @param tx_rnbase_util_FormatUtil $formatter
     */
    private function prepareLinks($betset, $marker, &$markerArray, &$subpartArray, &$wrappedSubpartArray, $confId, &$formatter)
    {
        $currItem = isset($this->options['currItem']) ? $this->options['currItem'] : false;
        // Link bauen, wenn: kein $currItem oder $currItem != $betset
        $linkId = 'scope';
        if (!intval($betset->getProperty('isCurrent'))) {
            $this->initLink($markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId, $linkId, $marker, array(
                'betset' => $betset->uid,
            ));
        } else {
            $linkMarker = $marker.'_'.strtoupper($linkId).'LINK';
            $remove = intval($formatter->getConfigurations()->get($confId.'links.'.$linkId.'.removeIfDisabled'));
            $this->disableLink($markerArray, $subpartArray, $wrappedSubpartArray, $linkMarker, $remove > 0);
        }
    }

    /**
     * Initialisiert die Labels für die Club-Klasse.
     *
     * @param tx_rnbase_util_FormatUtil $formatter
     * @param array $defaultMarkerArr
     */
    public function initLabelMarkers(&$formatter, $confId, $defaultMarkerArr = 0, $marker = 'ROUND')
    {
        return $this->prepareLabelMarkers('tx_t3sportsbet_models_betset', $formatter, $confId, $defaultMarkerArr, $marker);
    }

    /**
     * Returns a List-Marker instance.
     *
     * @return tx_rnbase_util_ListMarker
     */
    private function _getListMarker()
    {
        return tx_rnbase::makeInstance('tx_rnbase_util_ListMarker');
    }
}
