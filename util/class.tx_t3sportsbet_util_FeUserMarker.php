<?php

use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Frontend\Marker\FormatUtil;
use Sys25\RnBase\Frontend\Marker\SimpleMarker;
use Sys25\RnBase\Frontend\Marker\Templates;

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
 * Diese Klasse ist für die Erstellung von Markerarrays für FE User verantwortlich.
 */
class tx_t3sportsbet_util_FeUserMarker extends SimpleMarker
{
    /**
     * @param string $template das HTML-Template
     * @param FeUser $feuser The fe user
     * @param $formatter der zu verwendente Formatter
     * @param string $confId Pfad der TS-Config des Objekt, z.B. 'listView.event.'
     * @param $marker Name des Markers für ein Object, z.B. FEUSER
     *            Von diesem String hängen die entsprechenden weiteren Marker ab: ###FEUSER_NAME###
     *
     * @return string das geparste Template
     */
    public function parseTemplate($template, &$feuser, &$formatter, $confId, $marker = 'FEUSER')
    {
        $template = parent::parseTemplate($template, $feuser, $formatter, $confId, $marker);
        $markerArray = [];
        $wrappedSubpartArray = [];
        $subpartArray = [];
        $this->prepareLinks($feuser, $marker, $markerArray, $subpartArray, $wrappedSubpartArray, $confId, $formatter, $template);

        $out = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    /**
     * Links vorbereiten.
     *
     * @param FeUser $profile
     * @param string $marker
     * @param array $markerArray
     * @param array $wrappedSubpartArray
     * @param string $confId
     * @param FormatUtil $formatter
     */
    protected function prepareLinks($feuser, $marker, &$markerArray, &$subpartArray, &$wrappedSubpartArray, $confId, $formatter, $template)
    {
        $this->initLink($markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId, 'bets', $marker, [
            'feuserId' => $feuser ? $feuser->getUid() : 0,
        ]);
    }
}
