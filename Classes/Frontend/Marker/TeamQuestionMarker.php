<?php

namespace Sys25\T3sportsbet\Frontend\Marker;

use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\FormatUtil;
use Sys25\RnBase\Frontend\Marker\ListBuilder;
use Sys25\RnBase\Frontend\Marker\SimpleMarker;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Utility\Logger;
use Sys25\T3sportsbet\Model\TeamQuestion;
use Sys25\T3sportsbet\Utility\ServiceRegistry as BetServiceRegistry;
use System25\T3sports\Frontend\Marker\TeamMarker;
use System25\T3sports\Utility\ServiceRegistry;
use Throwable;
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
 * Handle team questions.
 */
class TeamQuestionMarker extends BaseMarker
{
    public static $simpleMarker;

    public static $teamMarker;
    private $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    /**
     * @param string $template das HTML-Template
     * @param TeamQuestion $item
     * @param FormatUtil $formatter der zu verwendente Formatter
     * @param string $confId Pfad der TS-Config des Vereins, z.B. 'listView.round.'
     * @param string $marker Name des Markers für die Tipprunde, z.B. ROUND
     *
     * @return string das geparste Template
     */
    public function parseTemplate($template, $item, $formatter, $confId, $marker = 'TEAMBET')
    {
        if (!is_object($item)) {
            // Ist kein Verein vorhanden wird ein leeres Objekt verwendet.
            $item = self::getEmptyInstance(TeamQuestion::class);
        }
        $feuser = $this->options['feuser'];

        $this->prepare($item, $template, $marker);
        // Es wird das MarkerArray mit den Daten des Tips gefüllt.
        $ignore = self::findUnusedCols($item->getProperty(), $template, $marker);
        $markerArray = $formatter->getItemMarkerArrayWrapped($item->getProperty(), $confId, $ignore, $marker.'_', $item->getColumnNames());

        $template = $this->handleStatePart($template, $item, $feuser, $formatter);

        if ($this->containsMarker($template, $marker.'_TEAMS')) {
            $template = $this->addTeams($template, $item, $formatter, $confId.'team.', $marker.'_TEAM');
        }

        if ($this->containsMarker($template, $marker.'_BET_')) {
            $template = $this->addBet($template, $item, $feuser, $formatter, $confId.'bet.', $marker.'_BET');
        }
        if ($this->containsMarker($template, $marker.'_TREND_')) {
            $template = $this->addTrend($template, $item, $feuser, $formatter, $confId.'trend.', $marker.'_TREND');
        }

        $wrappedSubpartArray = $subpartArray = [];
        $wrappedSubpartArray['###'.$marker.'_TREND###'] = ['', ''];
        $out = Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    /**
     * @return SimpleMarker
     */
    private static function getSimpleMarker()
    {
        if (!self::$simpleMarker) {
            self::$simpleMarker = tx_rnbase::makeInstance(SimpleMarker::class);
        }

        return self::$simpleMarker;
    }

    /**
     * @return TeamMarker
     */
    private static function getTeamMarker()
    {
        if (!self::$teamMarker) {
            self::$teamMarker = tx_rnbase::makeInstance(TeamMarker::class);
        }

        return self::$teamMarker;
    }

    /**
     * Add team selection.
     *
     * @param TeamQuestion $teamQuestion
     * @param string $template
     * @param FormatUtil $formatter
     * @param string $confId
     * @param string $marker
     *
     * @return string
     */
    private function addBet($template, TeamQuestion $item, $feuser, $formatter, $confId, $marker)
    {
        $srv = BetServiceRegistry::getTeamBetService();
        $bet = $srv->getTeamBet($item, $feuser);

        $template = self::getSimpleMarker()->parseTemplate($template, $bet, $formatter, $confId, $marker);
        if ($this->containsMarker($template, $marker.'_TEAM_')) {
            // Der Marker liefert eine Fehlermeldung, wenn das Team nicht valid ist
            if ($bet->getTeam()->isValid()) {
                $template = self::getTeamMarker()->parseTemplate($template, $bet->getTeam(), $formatter, $confId.'team.', $marker.'_TEAM');
            }
        }

        return $template;
    }

    /**
     * Add bet trend.
     *
     * @param TeamQuestion $item
     * @param string $template
     * @param FormatUtil $formatter
     * @param string $confId
     * @param string $marker
     *
     * @return string
     */
    private function addTrend($template, TeamQuestion $item, $feuser, $formatter, $confId, $markerPrefix)
    {
        $trendData = BetServiceRegistry::getTeamBetService()->getBetTrend($item);
        // Jetzt die TeamDaten einbauen
        $teams = [];
        for ($i = 0, $cnt = count($trendData); $i < $cnt; ++$i) {
            $teamId = $trendData[$i]['team'];
            $team = ServiceRegistry::getTeamService()->getTeam($teamId);
            if (!$team) {
                continue;
            }
            $team->setProperty(array_merge($team->getProperty(), $trendData[$i]));
            $teams[] = $team;
        }
        if ($this->containsMarker($template, $markerPrefix.'_TEAM_')) {
            $listBuilder = tx_rnbase::makeInstance(ListBuilder::class);
            $template = $listBuilder->render($teams, false, $template, TeamMarker::class, $confId.'team.', $markerPrefix.'_TEAM', $formatter);
        }

        if ($this->containsMarker($template, $markerPrefix.'_CHART')) {
            // pbimagegraph is not supported anymore
            // TODO: implement JS graph
            try {
                $markerArray = [];
                $markerArray['###'.$markerPrefix.'_CHART###'] = '';
                $template = Templates::substituteMarkerArrayCached($template, $markerArray); // , $wrappedSubpartArray);
            } catch (Throwable $e) {
                Logger::warn('Chart creation failed!', 'cfc_league_fe', [
                    'Exception' => $e->getMessage(),
                ]);
            }
        }

        return $template;
    }

    /**
     * @param TeamQuestion $item
     * @param \System25\T3sports\Model\Team[] $teams
     */
    private function makeChartDataProvider(TeamQuestion $item, $teams)
    {
        $dp = tx_rnbase::makeInstance('tx_rnbase_plot_DataProvider');
        $dp->setChartTitle($item->getQuestion());
        $dataSet = [];
        foreach ($teams as $team) {
            $data = [];
            $data['x'] = $team->getProperty('name');
            $data['y'] = $team->getProperty('betcount');
            $dataSet[] = $data;
        }
        $plotId = $dp->addPlot();
        $dp->addDataSet($plotId, $dataSet);

        return $dp;
    }

    /**
     * Set state subpart.
     *
     * @param TeamQuestion $teamQuestion
     * @param string $template
     * @param FormatUtil $formatter
     * @param string $confId
     * @param string $marker
     *
     * @return string
     */
    private function handleStatePart($template, TeamQuestion $teamQuestion, $feuser, $formatter)
    {
        $subpartArray = [];
        $subpartArray['###BETSTATUS_OPEN###'] = '';
        $subpartArray['###BETSTATUS_CLOSED###'] = '';
        $srv = BetServiceRegistry::getTeamBetService();
        $state = $srv->getTeamQuestionStatus($teamQuestion, $feuser);

        // Notwendig, damit der Submit-Button eingeblendet wird. Wird im View ausgewertet.
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

    /**
     * Hinzufügen der Teams.
     *
     * @param string $template HTML-Template
     * @param TeamQuestion $item
     * @param FormatUtil $formatter
     * @param string $confId Config-String
     * @param string $markerPrefix
     */
    private function addTeams($template, TeamQuestion $item, $formatter, $confId, $markerPrefix)
    {
        $children = BetServiceRegistry::getTeamBetService()->getTeams4TeamQuestion($item);
        // Den aktuellen Tip des Users mitgeben
        $options = [];
        $options['teambet'] = $this->findCurrentBet($item, $this->options['feuser']);
        $listBuilder = tx_rnbase::makeInstance(ListBuilder::class);
        $out = $listBuilder->render($children, false, $template, TeamMarker::class, $confId, $markerPrefix, $formatter, $options);

        return $out;
    }

    /**
     * Returns the UID of current teambet for given user.
     *
     * @param FeUser $feuser
     * @param TeamQuestion $item
     */
    private function findCurrentBet(TeamQuestion $item, $feuser)
    {
        if (!$feuser) {
            return 0;
        }
        $bet = BetServiceRegistry::getTeamBetService()->getTeamBet($item, $feuser);

        return $bet->isPersisted() ? $bet->getTeamUid() : 0;
    }

    /**
     * @param TeamQuestion $item
     * @param string $template
     * @param string $marker
     */
    private function prepare(TeamQuestion $item, $template, $marker)
    {
        $item->setProperty('openuntiltstamp', $item->getOpenUntilTstamp());
    }
}
