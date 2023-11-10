<?php

namespace Sys25\T3sportsbet\Frontend\Marker;

use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Domain\Repository\FeUserRepository;
use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\FormatUtil;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\T3sportsbet\Model\Bet;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use System25\T3sports\Frontend\Marker\MatchMarker as T3SMatchMarker;
use System25\T3sports\Model\Fixture;
use System25\T3sports\Model\Repository\MatchRepository;
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
 * Diese Klasse ist für die Erstellung von Markerarrays der Tipprunden verantwortlich.
 */
class MatchMarker extends BaseMarker
{
    public static $betMarker;

    private $request;
    private $matchMarker;
    private $options;
    private $feuserRepo;
    private $matchRepo;
    private $betSrv;

    public function __construct($options = [])
    {
        $this->options = $options;
        $this->request = $options['request'];
        $this->matchMarker = tx_rnbase::makeInstance(T3SMatchMarker::class);
        $this->feuserRepo = new FeUserRepository();
        $this->matchRepo = new MatchRepository();
        $this->betSrv = ServiceRegistry::getBetService();
    }

    /**
     * @return BetMarker
     */
    private static function getBetMarker()
    {
        if (!self::$betMarker) {
            self::$betMarker = tx_rnbase::makeInstance(BetMarker::class);
        }

        return self::$betMarker;
    }

    /**
     * @param string $template das HTML-Template
     * @param Fixture $match das Spiel
     * @param FormatUtil $formatter der zu verwendente Formatter
     * @param string $matchConfId Pfad der TS-Config des Spiels, z.B. 'listView.match.'
     * @param string $matchMarker Name des Markers für ein Spiel, z.B. MATCH
     *
     * @return string das geparste Template
     */
    public function parseTemplate($template, $match, $formatter, $confId, $marker = 'MATCH')
    {
        /** @var BetSet $betset */
        $betset = $this->options['betset'];
        $feuser = $this->options['feuser'];

        // Set some registers for TS
        $GLOBALS['TSFE']->register['T3SPORTS_MATCHUID'] = $match->getUid();
        $GLOBALS['TSFE']->register['T3SPORTS_MATCHSTATE'] = $match->getProperty('status');
        $GLOBALS['TSFE']->register['T3USERS_USERUID'] = is_object($feuser) ? $feuser->getUid() : 0;
        $GLOBALS['TSFE']->register['T3SPORTSBET_BETSETUID'] = $betset->getUid();
        $GLOBALS['TSFE']->register['T3SPORTSBET_BETSETSTATUS'] = $betset->getProperty('status');
        $GLOBALS['TSFE']->register['T3SPORTSBET_BETSTATUS'] = $betset->getMatchState($match);

        // Die Tipptendenz mit einblenden
        if (self::containsMarker($template, $marker.'_TREND')) {
            $this->addBetTrend($betset, $match);
        }
        if (self::containsMarker($template, $marker.'_STATS')) {
            $this->addBetStats($betset, $match);
        }

        // Für T3sports muss der Qualifier geändert werden, damit die Verlinkung klappt
        $formatter->getConfigurations()->setQualifier('cfc_league_fe');
        $template = $this->matchMarker->parseTemplate($template, $match, $formatter, $confId, $marker);
        $formatter->getConfigurations()->setQualifier($formatter->getConfigurations()
            ->get('qualifier'));

        $this->pushTT('setForm');

        $bet = $this->betSrv->getBet($betset, $match, $feuser);
        $template = $this->setForm($template, $betset, $bet, $feuser, $formatter);
        $this->pullTT();
        $template = self::getBetMarker()->parseTemplate($template, $bet, $formatter, $confId.'bet.', $marker.'_BET');

        return $template;
    }

    /**
     * Tiptrend für das Spiel einsetzen.
     *
     * @param BetSet $betset
     * @param Fixture $match
     */
    public function addBetTrend($betset, $match)
    {
        $srv = ServiceRegistry::getBetService();
        $trend = $srv->getBetTrend($betset, $match);
        $match->setProperty(array_merge($match->getProperty(), $trend));
    }

    /**
     * Tipstatistik für das Spiel einsetzen.
     * Diese Daten sind erst nach der Auswertung des Spiels möglich.
     *
     * @param BetSet $betset
     * @param Fixture $match
     */
    public function addBetStats($betset, $match)
    {
        $srv = ServiceRegistry::getBetService();
        $trend = $srv->getBetStats($betset, $match);

        $match->setProperty(array_merge($match->getProperty(), $trend));
    }

    /**
     * Render form.
     *
     * @param string $template
     * @param BetSet $betset
     * @param Bet $bet
     * @param FeUser $feuser
     * @param FormatUtil $formatter
     *
     * @return string
     */
    public function setForm($template, $betset, $bet, $feuser, $formatter)
    {
        $subpartArray['###BETSTATUS_OPEN###'] = '';
        $subpartArray['###BETSTATUS_CLOSED###'] = '';
        $subpartArray['###BETSTATUS_FINISHED###'] = '';
        // Ohne FE-User setzen wir die Anzeige immer auf CLOSED
        // Gleiches gilt, wenn der aktuelle User != FE-User ist
        $state = 'CLOSED';
        if ($feuser) {
            $state = $betset->getMatchState($this->matchRepo->findByUid($bet->getFixtureUid()));
            if ('OPEN' == $state) {
                // Prüfen, ob der aktuelle User seinen eigenen Tip bearbeiten will
                $currUser = $this->feuserRepo->getCurrent();
                if (!($currUser && $currUser->getUid() == $feuser->getUid())) {
                    $state = 'CLOSED';
                }
            }
        }
        // Hier benötigen wir eigentlich einen Observer, dem wir sagen, daß ein Spiel offen ist. Wir setzen das jetzt
        // einfach mal in die Config...
        if ('OPEN' == $state) {
            $formatter->getConfigurations()
                ->getViewData()
                ->offsetSet('MATCH_STATE', 'OPEN');
        }
        $markerArray = [];
        $subTemplate = Templates::getSubpart($template, '###BETSTATUS_'.$state.'###');
        $subpartArray['###BETSTATUS_'.$state.'###'] = $subTemplate;
        $out = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray); // , $wrappedSubpartArray);

        return $out;
    }
}
