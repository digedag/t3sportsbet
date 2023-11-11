<?php

namespace Sys25\T3sportsbet\Module\Handler;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2023 Rene Nitzsche (rene@system25.de)
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

use Sys25\RnBase\Backend\Module\IModFunc;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\T3General;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use System25\T3sports\Model\Fixture;
use Throwable;
use tx_rnbase;

class MatchMoveHandler
{
    /**
     * @return MatchMoveHandler
     */
    public static function getInstance()
    {
        return tx_rnbase::makeInstance(self::class);
    }

    /**
     * @param IModule $mod
     */
    public function handleRequest(IModule $mod)
    {
        $isCutted = T3General::_GP('doCutMatch');
        if ($isCutted) {
            return $this->handleCut($isCutted, $mod);
        }
        $isReleased = T3General::_GP('doReleaseMatch');
        if ($isReleased) {
            return $this->handleCut('', $mod);
        }
        // Jetzt noch der Insert
        $isPasted = T3General::_GP('doPasteMatch');
        if ($isPasted) {
            return $this->handlePaste($isPasted, $mod);
        }
    }

    /**
     * @param int $newBetsetUid
     * @param IModule $mod
     */
    private function handlePaste($newBetsetUid, IModule $mod)
    {
        $currentToken = $this->getCurrentMatch($mod);
        list($oldBetsetUid, $matchUid) = Strings::intExplode('_', $currentToken);

        try {
            ServiceRegistry::getBetService()->moveMatch($newBetsetUid, $oldBetsetUid, $matchUid);
        } catch (Throwable $e) {
            return $mod->getDoc()->section('###LABEL_ERROR###', $e->getMessage(), 0, 1, IModFunc::ICON_FATAL);
        }
        // Reset cutted matches
        $this->handleCut(0, $mod);

        return $mod->getDoc()->section('###LABEL_MSG_MATCHMOVED###', '', 0, 1, IModFunc::ICON_INFO);
    }

    /**
     * @param IModule $mod
     */
    private function handleCut($matchToken, $mod)
    {
        // Dieses Spiel in den Speicher legen
        $key = 'doCutMatch';
        $changed = [$key => $matchToken];
        BackendUtility::getModuleData([
            $key => '',
        ], $changed, $mod->getName());
    }

    private function getCurrentMatch($mod)
    {
        $key = 'doCutMatch';
        $arr = BackendUtility::getModuleData([
            $key => '',
        ], [], $mod->getName());

        return $arr[$key] ?? null;
    }

    /**
     * @param Fixture $item
     * @param IModule $mod
     */
    public function makeCutLink($item, $betset, $mod)
    {
        $currentMatch = $this->getCurrentMatch($mod);
        $options = [];
        $key = $betset->getUid().'_'.$item->getUid();
        $ret = '';
        if ($currentMatch != $key) {
            $options['icon'] = 'actions-edit-cut';
            $ret .= $mod->getFormTool()->createSubmit('doCutMatch', $key, '', $options);
        } else {
            $label = '';
            $options['icon'] = 'actions-edit-cut-release';
            $ret .= $mod->getFormTool()->createModuleLink(['doReleaseMatch' => 'true'], $mod->getPid(), $label, $options);
        }

        return $ret;
    }

    /**
     * @param BetSet $item
     * @param IModule $mod
     */
    public function makePasteButton($item, $mod)
    {
        $ret = '';
        $currentToken = $this->getCurrentMatch($mod);
        if (!$currentToken) {
            return $ret;
        }
        list($currentBetsetUid, $currentMatchUid) = Strings::intExplode('_', $currentToken);

        $uids = ServiceRegistry::getBetService()->findMatchUidsByBetSet($item);
        if (in_array($currentMatchUid, $uids)) {
            return $ret;
        }

        $match = tx_rnbase::makeInstance('tx_cfcleague_models_Match', $currentMatchUid);
        $matchInfo = $match->getHome()->getName().'-'.$match->getGuest()->getName();
        $matchInfo = sprintf($GLOBALS['LANG']->getLL('label_paste_match'), $matchInfo);
        $options = [];
        $options['confirm'] = $GLOBALS['LANG']->getLL('label_msg_paste_match');
        $options['hover'] = $matchInfo;
        $label = '<span class="t3-icon t3-icon-actions t3-icon-actions-document t3-icon-document-paste-after"></span>';
        $label .= $matchInfo.'<br />';
        $ret .= $mod->getFormTool()->createModuleLink(['doPasteMatch' => $item->getUid()], $mod->getPid(), $label, $options);

        return $ret;
    }
}
