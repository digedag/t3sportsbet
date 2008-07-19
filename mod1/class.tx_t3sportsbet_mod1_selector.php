<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');
tx_div::load('tx_rnbase_util_Misc');

/**
 * Die Klasse stellt Auswahlmenus zur Verfügung
 */
class tx_t3sportsbet_mod1_selector{
	var $doc, $MCONF;

	/**
	 * Initialisiert das Objekt mit dem Template und der Modul-Config.
	 */
	function init($doc, $MCONF){
		$this->doc = $doc;
		$this->MCONF = $MCONF;
		$this->formTool = t3lib_div::makeInstance('tx_rnbase_util_FormTool');
		$this->formTool->init($this->doc);
		tx_rnbase_util_Misc::prepareTSFE();
	}

	/**
	 * Darstellung der Select-Box mit allen Ligen der übergebenen Seite. Es wird auf die aktuelle Liga eingestellt.
	 * @return tx_t3sportsbet_models_betgame den aktuellen Wettbewerb als Objekt oder 0
	 */
	function showGameSelector(&$content,$pid,$games=0){
		// Wenn vorhanden, nehmen wir die übergebenen Wettbewerbe, sonst schauen wir auf der aktuellen Seite nach
		$games = $games ? $games : $this->findGames($pid);
		$this->GAME_MENU = Array (
			'game' => array()
		);
		$idxGames = array();
		foreach($games as $game){
			$idxGames[$game->uid] = $game;
			$this->GAME_MENU['game'][$game->uid] = $game->getName();
		}
		$this->GAME_SETTINGS = t3lib_BEfunc::getModuleData(
			$this->GAME_MENU,t3lib_div::_GP('SET'),$this->MCONF['name'] // Das ist der Name des Moduls
		);

		$menu = t3lib_BEfunc::getFuncMenu(
			$pid,'SET[game]',$this->GAME_SETTINGS['game'],$this->GAME_MENU['game']
		);
		// In den Content einbauen
		// Zusätzlich noch einen Edit-Link setzen
		if($menu) {
			$link = $this->formTool->createEditLink('tx_t3sportsbet_betgames', $this->GAME_SETTINGS['game'],'');
			$link .= $this->formTool->createNewLink('tx_t3sportsbet_betgames', $pid,'');
			$menu .= '</td><td style="width:90px; padding-left:10px;">' . $link;
		}
		$content.=$this->doc->section('',$this->doc->funcMenu($headerSection,$menu));

		return $this->GAME_SETTINGS['game'] ? $idxGames[$this->GAME_SETTINGS['game']] :0;
	}

	/**
	 * Returns all rounds of current bet game
	 *
	 * @param string $content
	 * @param int $pid
	 * @param tx_t3sportsbet_models_betgame $game
	 * @return tx_t3sportsbet_models_betset
	 */
	function showRoundSelector(&$content,$pid,$game){
		$this->ROUND_MENU = Array (
			'betset' => array()
		);

		$rounds = $game->getBetSets();
		$empty = !count($rounds);
		if($empty) {
			$menu .= $GLOBALS['LANG']->getLL('msg_no_betset_found');
		}
		else {
			$idxRounds = array();
			foreach($rounds as $round){
				$idxRounds[$round->uid] = $round;
				$this->ROUND_MENU['betset'][$round->uid] = $round->getName();
			}
			$this->ROUND_SETTINGS = t3lib_BEfunc::getModuleData(
				$this->ROUND_MENU,t3lib_div::_GP('SET'),$this->MCONF['name'] // Das ist der Name des Moduls
			);
			$menu = t3lib_BEfunc::getFuncMenu(
				$pid,'SET[betset]',$this->ROUND_SETTINGS['betset'],$this->ROUND_MENU['betset']
			);
		}


		// In den Content einbauen
		// Spielrunden sind keine Objekt, die bearbeitet werden können
		if($menu) {
			$link = '';
			if(!$empty)
				$link .= $this->formTool->createEditLink('tx_t3sportsbet_betsets', $this->GAME_SETTINGS['betset'],'');
			$link .= $this->formTool->createNewLink('tx_t3sportsbet_betsets', $pid,'');
			$menu .= '</td><td style="width:90px; padding-left:10px;">' . $link;
		}
		$content.=$this->doc->section('',$this->doc->funcMenu($headerSection,$menu));
		return $this->ROUND_SETTINGS['betset'] ? $idxRounds[$this->ROUND_SETTINGS['betset']] :0;
	}

	/**
	 * Liefert die Tipspiele der aktuellen Seite.
	 * @return ein Array mit Rows
	 */
	function findGames($pid){
		$options['where'] = 'pid="'.$pid.'"';
		$options['orderby'] = 'sorting';
		$options['wrapperclass'] = 'tx_t3sportsbet_models_betgame';
		return tx_rnbase_util_DB::doSelect('*', 'tx_t3sportsbet_betgames', $options, 0);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_selector.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_selector.php']);
}


?>
