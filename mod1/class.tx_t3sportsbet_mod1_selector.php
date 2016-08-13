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

tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Die Klasse stellt Auswahlmenus zur Verfügung
 */
class tx_t3sportsbet_mod1_selector{
	var $doc, $MCONF;
	private $formTool;

	/**
	 * Initialisiert das Objekt mit dem Template und der Modul-Config.
	 */
	public function init($doc, tx_rnbase_mod_IModule $module){
		$this->doc = $doc;
		$this->MCONF['name'] = $module->getName(); // deprecated
		$this->modName = $module->getName();
		$this->module = $module;
		$this->formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
		$this->formTool->init($this->doc, $module);
		tx_rnbase_util_Misc::prepareTSFE();
	}

	/**
	 * Darstellung der Select-Box mit allen Ligen der übergebenen Seite. Es wird auf die aktuelle Liga eingestellt.
	 * @return tx_t3sportsbet_models_betgame den aktuellen Wettbewerb als Objekt oder 0
	 */
	public function showGameSelector(&$content,$pid,$games=0){
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
			$links = $this->formTool->createEditLink('tx_t3sportsbet_betgames', $this->GAME_SETTINGS['game'],'');
			$links .= $this->formTool->createNewLink('tx_t3sportsbet_betgames', $pid,'');
			$menu = '<div class="cfcselector"><div class="selector">' . $menu . '</div><div class="links">' . $links . '</div></div>';
//			$menu .= '</td><td style="width:90px; padding-left:10px;">' . $link;
		}
		$content.=$menu;
//		$content.=$this->doc->section('',$this->doc->funcMenu($headerSection,$menu));

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
	public function showRoundSelector(&$content,$pid,$game){
		$rounds = $game->getBetSets();
		$idxRounds = array();
		$entries = array();
		foreach($rounds as $round){
			$idxRounds[$round->getUid()] = $round;
			$entries[$round->getUid()] = $round->getName() . ' (' . $GLOBALS['LANG']->getLL('tx_t3sportsbet_module.betStatus_'.$round->getStatus()) . ')';
		}
		$menuData = $this->getFormTool()->showMenu($pid, 'betset', $this->modName, $entries);

		if($menuData['value'] > 0) {
			$menu = $menuData['menu'];
			if($menu) {
				$links = '';
				if(!$empty)
					$links .= $this->formTool->createEditLink('tx_t3sportsbet_betsets', $this->ROUND_SETTINGS['betset'],'');
					$params['params'] = '&betgame='.$game->getUid();
					$params['params'] .= '&round='.($game->getBetSetSize()+1);
					$params['title'] = $GLOBALS['LANG']->getLL('label_create_betset');
					$links .= $this->getFormTool()->createNewLink('tx_t3sportsbet_betsets', $pid,'',$params);
					$menu = '<div class="cfcselector"><div class="selector">' . $menu . '</div><div class="links">' . $links . '</div></div>';
					//			$menu .= '</td><td style="width:90px; padding-left:10px;">' . $link;
					//t3lib_div::debug($link, 'tx_t3sportsbet_mod1_selector'); // TODO: remove me
			}
		}
		else
			$menu .= $GLOBALS['LANG']->getLL('msg_no_betset_found');

		// In den Content einbauen
		// Spielrunden sind keine Objekte, die bearbeitet werden können
		$content.=$menu;
		if(count($idxRounds))
			return $menuData['value'] ? $idxRounds[$menuData['value']] :0;

	}

	/**
	 * Liefert die Tipspiele der aktuellen Seite.
	 * @return ein Array mit Rows
	 */
	private function findGames($pid){
		$options['where'] = 'pid="'.$pid.'"';
		$options['orderby'] = 'sorting';
		$options['wrapperclass'] = 'tx_t3sportsbet_models_betgame';
		return tx_rnbase_util_DB::doSelect('*', 'tx_t3sportsbet_betgames', $options, 0);
	}

	private function getFormTool() {
		return $this->formTool;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_selector.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_selector.php']);
}


?>
