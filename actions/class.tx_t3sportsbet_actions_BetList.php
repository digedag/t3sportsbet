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
tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_t3sportsbet_models_betgame');
tx_rnbase::load('tx_t3sportsbet_models_betset');
tx_rnbase::load('tx_t3users_models_feuser');
tx_rnbase::load('tx_t3sportsbet_util_ScopeController');

/**
 * Der View zeigt Tiprunden an und speichert Veränderungen.
 */
class tx_t3sportsbet_actions_BetList extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    /**
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     *
     * @return string error msg or null
     */
    protected function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $configurations = $request->getConfigurations();
        $feuser = tx_t3users_models_feuser::getCurrent();
        $viewData = $request->getViewContext();
        $viewData->offsetSet('currfeuser', $feuser);

        $scopeArr = tx_t3sportsbet_util_ScopeController::handleCurrentScope($request, []);
        $betgames = tx_t3sportsbet_util_ScopeController::getBetgamesFromScope($scopeArr['BETGAME_UIDS']);
        $rounds = $this->getRoundsFromScope($scopeArr['BETSET_UIDS']);
        $this->handleSubmit($feuser, $viewData);

        if ($configurations->get('betlist.feuserFromRequestAllowed')) {
            // Der Nutzer, dessen Tips gezeigt werden kann per Request übergeben werden
            $uid = (int) $parameters->offsetGet('feuserId');
            if ($uid) {
                $feuser = tx_t3users_models_feuser::getInstance($uid);
            }
        }

        // Über die viewdata können wir Daten in den View transferieren
        $viewData->offsetSet('betgame', $betgames[0]);
        $viewData->offsetSet('rounds', $rounds);
        $viewData->offsetSet('feuser', $feuser);

        return null;
    }

    protected function getRoundsFromScope($uids)
    {
        $rounds = [];
        if (!$uids) {
            return $rounds;
        }
        $uids = Tx_Rnbase_Utility_Strings::intExplode(',', $uids);
        for ($i = 0, $cnt = count($uids); $i < $cnt; ++$i ) {
            $rounds[] = tx_t3sportsbet_models_betset::getBetsetInstance($uids[$i]);
        }

        return $rounds;
    }

    protected function handleSubmit($feuser, $viewData)
    {
        if (!$feuser) {
            return; // Nicht angemeldet
        }
        $srv = tx_t3sportsbet_util_serviceRegistry::getBetService();
        $data = \Tx_Rnbase_Utility_T3General::_GP('betset');
        if (!is_array($data)) {
            return;
        }
        tx_rnbase::load('tx_cfcleaguefe_models_match');
        $saveCnt = 0;
        // Die Tips speichern
        foreach ($data as $betsetUid => $matchArr) {
            $betset = tx_t3sportsbet_models_betset::getBetsetInstance($betsetUid);
            if (!$betset->isValid() || $betset->isFinished()) {
                continue;
            }
            foreach ($matchArr as $matchUid => $betArr) {
                if ('teambet' == $matchUid) {
                    $saveCnt += $this->saveTeamBet($betArr, $feuser);

                    continue;
                }

                $match = tx_cfcleaguefe_models_match::getMatchInstance($matchUid);
                list($betUid, $betData) = each($betArr);
                $saveCnt += $srv->saveOrUpdateBet($betset, $match, $feuser, $betUid, $betData);
            }
        }
        // Vermerken, wieviele Spiele gespeichert wurden.
        $viewData->offsetSet('saved', $saveCnt);
    }

    private function saveTeamBet($betArr, $feuser)
    {
        $ret = 0;
        foreach ($betArr as $betQuestionUid => $betData) {
            $betQuestion = tx_rnbase::makeInstance('tx_t3sportsbet_models_teamquestion', intval($betQuestionUid));
            if (!$betQuestion->isValid()) {
                return 0;
            }
            list($betUid, $team) = each($betData);
            $srv = tx_t3sportsbet_util_serviceRegistry::getTeamBetService();
            $ret += $srv->saveOrUpdateBet($betQuestion, $feuser, $betUid, $team);
        }

        return $ret;
    }

    protected function getTemplateName()
    {
        return 'betlist';
    }

    protected function getViewClassName()
    {
        return 'tx_t3sportsbet_views_BetList';
    }
}
