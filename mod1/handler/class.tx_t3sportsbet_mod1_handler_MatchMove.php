<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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


class tx_t3sportsbet_mod1_handler_MatchMove {
	/**
	 * @return tx_t3sportsbet_mod1_handler_MatchMove
	 */
	public static function getInstance() {
		return tx_rnbase::makeInstance('tx_t3sportsbet_mod1_handler_MatchMove');
	}
	/**
	 * 
	 * @param tx_rnbase_mod_IModule $mod
	 */
	public function handleRequest($mod) {
		$isCutted = t3lib_div::_GP('doCutMatch');
		if($isCutted) {
			return $this->handleCut($isCutted, $mod);
		}
		$isReleased = t3lib_div::_GP('doReleaseMatch');
		if($isReleased) {
			return $this->handleCut('', $mod);
		}
		// Jetzt noch der Insert
		$isPasted = t3lib_div::_GP('doPasteMatch');
		if($isPasted) {
			return $this->handlePaste($isPasted, $mod);
		}
	}
	/**
	 * 
	 * @param int $betset
	 * @param tx_rnbase_mod_IModule $mod
	 */
	private function handlePaste($newBetsetUid, $mod) {
		$currentToken = $this->getCurrentMatch($mod);
		list($oldBetsetUid, $matchUid) = t3lib_div::intExplode('_', $currentToken);
		try {
			tx_t3sportsbet_util_serviceRegistry::getBetService()->moveMatch($newBetsetUid, $oldBetsetUid, $matchUid);
		}
		catch(Exception $e) {
			return $mod->getDoc()->section('###LABEL_ERROR###',$e->getMessage(),0,1,ICON_FATAL);
		}
		// Reset cutted matches
		$this->handleCut(0, $mod);
		return $mod->getDoc()->section('###LABEL_MSG_MATCHMOVED###','',0,1,ICON_INFO);
	}
	/**
	 * 
	 * @param tx_rnbase_mod_IModule $mod
	 */
	private function handleCut($matchToken, $mod) {
		// Dieses Spiel in den Speicher legen
		$key = 'doCutMatch';
		$changed[$key] = $matchToken;
		t3lib_BEfunc::getModuleData(array ($key => ''), $changed, $mod->getName() );
	}
	private function getCurrentMatch($mod) {
		$key = 'doCutMatch';
		$arr = t3lib_BEfunc::getModuleData(array ($key => ''), array(), $mod->getName() );
		return $arr[$key];
	}
	/**
	 * 
	 * @param unknown_type $item
	 * @param tx_rnbase_mod_IModule $mod
	 */
	public function makeCutLink($item, $betset, $mod) {
		$currentMatch = $this->getCurrentMatch($mod);
		$options = array();
		$key = $betset->getUid().'_'.$item->getUid();
		if($currentMatch != $key) {
			$options['icon'] = 'clip_cut.gif';
			$ret .= $mod->getFormTool()->createSubmit('doCutMatch', $key,'',$options);
		}
		else {
			$label = '<span class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-cut-release"></span>';
			$ret .= $mod->getFormTool()->createLink('&doReleaseMatch=true', $mod->getPid(),$label,$options);
		}

		return $ret;
	}
	/**
	 * 
	 * @param tx_t3sportsbet_models_betset $item
	 * @param tx_rnbase_mod_IModule $mod
	 */
	public function makePasteButton($item, $mod) {
		$ret = '';
		$currentToken = $this->getCurrentMatch($mod);
		if(!$currentToken) return $ret;
		list($currentBetsetUid, $currentMatchUid) = t3lib_div::intExplode('_', $currentToken);

		$uids = tx_t3sportsbet_util_serviceRegistry::getBetService()->findMatchUidsByBetSet($item);
		if(t3lib_div::inArray($uids, $currentMatchUid)) return $ret;

		$options = array();
		$options['confirm'] = $GLOBALS['LANG']->getLL('label_msg_paste_match');
		$options['hover'] = '###LABEL_PASTE_MATCH###';
		$label = '<span class="t3-icon t3-icon-actions t3-icon-actions-document t3-icon-document-paste-after"></span>';
		$label .= '###LABEL_PASTE_MATCH###<br />';
		$ret .= $mod->getFormTool()->createLink('&doPasteMatch='.$item->getUid(), $mod->getPid(),$label,$options);
		return $ret;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/handler/class.tx_t3sportsbet_mod1_handler_MatchMove.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/handler/class.tx_t3sportsbet_mod1_handler_MatchMove.php']);
}
?>