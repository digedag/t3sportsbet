<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * Handle team questions
 */
class tx_t3sportsbet_util_TeamQuestionMarker extends tx_rnbase_util_BaseMarker {
	static $simpleMarker = null;
	static $teamMarker = null;
	function __construct($options = array()) {
		$this->options = $options;
	}

	/**
	 * @param string $template das HTML-Template
	 * @param tx_t3sportsbet_models_teamquestion $item
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $confId Pfad der TS-Config des Vereins, z.B. 'listView.round.'
	 * @param string $marker Name des Markers für die Tipprunde, z.B. ROUND
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, $item, $formatter, $confId, $marker = 'TEAMBET') {
		if(!is_object($item)) {
			// Ist kein Verein vorhanden wird ein leeres Objekt verwendet.
			$item = self::getEmptyInstance('tx_t3sportsbet_models_teamquestion');
		}
		$feuser = $this->options['feuser'];

		$this->prepare($item, $template, $marker);
		// Es wird das MarkerArray mit den Daten des Tips gefüllt.
		$ignore = self::findUnusedCols($item->record, $template, $marker);
		$markerArray = $formatter->getItemMarkerArrayWrapped($item->record, $confId , $ignore, $marker.'_',$item->getColumnNames());

		$template = $this->handleStatePart($template, $item, $feuser, $formatter);

		if($this->containsMarker($template, $marker.'_TEAMS'))
			$template = $this->addTeams($template, $item, $formatter, $confId.'team.', $marker.'_TEAM');

		if($this->containsMarker($template, $marker.'_BET_'))
			$template = $this->addBet($template, $item, $feuser, $formatter, $confId.'bet.', $marker.'_BET');
		if($this->containsMarker($template, $marker.'_TREND_'))
			$template = $this->addTrend($template, $item, $feuser, $formatter, $confId.'trend.', $marker.'_TREND');

		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		return $out;
	}
	/**
	 * @return tx_rnbase_util_SimpleMarker
	 */
	private static function getSimpleMarker() {
		if(!self::$simpleMarker)
			self::$simpleMarker = tx_rnbase::makeInstance('tx_rnbase_util_SimpleMarker');
		return self::$simpleMarker;
	}
	/**
	 * @return tx_cfcleaguefe_util_TeamMarker
	 */
	private static function getTeamMarker() {
		if(!self::$teamMarker)
			self::$teamMarker = tx_rnbase::makeInstance('tx_cfcleaguefe_util_TeamMarker');
		return self::$teamMarker;
	}

	/**
	 * Add team selection
	 *
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @param string $template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * @return string
	 */
	private function addBet($template, $item, $feuser, $formatter, $confId, $marker) {
		$srv = tx_t3sportsbet_util_serviceRegistry::getTeamBetService();
		$bet = $srv->getTeamBet($item, $feuser);
		$template = self::getSimpleMarker()->parseTemplate($template, $bet, $formatter, $confId, $marker);
		if($this->containsMarker($template, $marker.'_TEAM_')) {
			$template = self::getTeamMarker()->parseTemplate($template, $bet->getTeam(), $formatter, $confId.'team.', $marker.'_TEAM');
		}
		return $template;
	}
	/**
	 * Add bet trend
	 *
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @param string $template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * @return string
	 */
	private function addTrend($template, $item, $feuser, $formatter, $confId, $markerPrefix) {
		$trendData = tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->getBetTrend($item);
		// Jetzt die TeamDaten einbauen
		$teams = array();
		for($i=0, $cnt=count($trendData); $i < $cnt; $i++) {
			$teamId = $trendData[$i]['team'];
			$team = tx_cfcleague_util_ServiceRegistry::getTeamService()->getTeam($teamId);
			if(!$team) continue;
			$team->record = array_merge($team->record, $trendData[$i]);
			$teams[] = $team;
		}
		if($this->containsMarker($template, $markerPrefix.'_TEAM_')) {
			$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
			$template = $listBuilder->render($teams,
							false, $template, 'tx_cfcleaguefe_util_TeamMarker',
							$confId.'team.', $markerPrefix.'_TEAM', $formatter, $options);
		}

		if($this->containsMarker($template, $markerPrefix.'_CHART')) {
			try {
				tx_rnbase::load('tx_rnbase_plot_Builder');
				$tsConf = $formatter->getConfigurations()->get($confId.'chart.');
				$dp = $this->makeChartDataProvider($teams);
				$markerArray['###'.$markerPrefix.'_CHART###'] = tx_rnbase_plot_Builder::getInstance()->make($tsConf, $dp);
				$template = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray); //, $wrappedSubpartArray);
			}
			catch(Exception $e) {
				$chart = 'Not possible';
				tx_rnbase::load('tx_rnbase_util_Logger');
				tx_rnbase_util_Logger::warn('Chart creation failed!', 'cfc_league_fe', array('Exception' => $e->getMessage()));
			}
		}
		
		return $template;
	}
	private function makeChartDataProvider($teams) {
		$dp =tx_rnbase::makeInstance('tx_rnbase_plot_DataProvider');
		$dataSet = array();
		foreach($teams As $team) {
			$data = array();
			$data['x'] = $team->record['name'];
			$data['y'] = $team->record['betcount'];
			$dataSet[] = $data;
		}
		$plotId = $dp->addPlot();
		$dp->addDataSet($plotId, $dataSet);
		return $dp;
	}

	/**
	 * Set state subpart
	 *
	 * @param tx_t3sportsbet_models_teamquestion $teamQuestion
	 * @param string $template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * @return string
	 */
	private function handleStatePart($template, $teamQuestion, $feuser, $formatter) {
		// 
		$subpartArray['###BETSTATUS_OPEN###'] = '';
		$subpartArray['###BETSTATUS_CLOSED###'] = '';
		$srv = tx_t3sportsbet_util_serviceRegistry::getTeamBetService();
		$state = $srv->getTeamQuestionStatus($teamQuestion, $feuser);

		$subTemplate = tx_rnbase_util_Templates::getSubpart($template,'###BETSTATUS_'.$state.'###');
		$subpartArray['###BETSTATUS_'.$state.'###'] = $subTemplate;
    $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray); //, $wrappedSubpartArray);
		return $out;
	}
	/**
	 * Hinzufügen der Teams.
	 * @param string $template HTML-Template
	 * @param tx_t3sportsbet_models_teamquestion $item
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId Config-String
	 * @param string $markerPrefix
	 */
	private function addTeams($template, $item, $formatter, $confId, $markerPrefix) {

		$children = tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->getTeams4TeamQuestion($item);
		// Den aktuellen Tip des Users mitgeben
		$options = array();
		$options['teambet'] = $this->findCurrentBet($item, $this->options['feuser']);
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$out = $listBuilder->render($children,
						false, $template, 'tx_cfcleaguefe_util_TeamMarker',
						$confId, $markerPrefix, $formatter, $options);
		return $out;
	}
	/**
	 * Returns the UID of current teambet for given user
	 * @param tx_t3users_models_feuser $feuser
	 * @param tx_t3sportsbet_models_teamquestion $item
	 */
	private function findCurrentBet($item, $feuser) {
		if(!$feuser) return 0;
		$bet = tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->getTeamBet($item, $feuser);
		return $bet->isPersisted() ? $bet->getTeamUid() : 0;
	}
	/**
	 * 
	 * @param tx_t3sportsbet_models_teamquestion $item
	 * @param string $template
	 * @param string $marker
	 */
	private function prepare($item, $template, $marker) {
		$item->record['openuntiltstamp'] = $item->getOpenUntilTstamp();
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_TeamQuestionMarker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/util/class.tx_t3sportsbet_util_TeamQuestionMarker.php']);
}
?>