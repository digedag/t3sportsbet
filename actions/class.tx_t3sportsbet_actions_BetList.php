<?php

use Sys25\RnBase\Domain\Repository\FeUserRepository;
use Sys25\RnBase\Frontend\Controller\AbstractAction;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\T3General;
use System25\T3sports\Model\Repository\MatchRepository;

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
 * Der View zeigt Tiprunden an und speichert Veränderungen.
 */
class tx_t3sportsbet_actions_BetList extends AbstractAction
{
    private $feuserRepo;
    private $matchRepo;

    public function __construct()
    {
        $this->feuserRepo = new FeUserRepository();
        $this->matchRepo = new MatchRepository();
    }

    /**
     * @param RequestInterface $request
     *
     * @return string error msg or null
     */
    protected function handleRequest(RequestInterface $request)
    {
        $parameters = $request->getParameters();
        $configurations = $request->getConfigurations();
        $feuser = $this->feuserRepo->getCurrent();
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
                $feuser = $this->feuserRepo->getInstance($uid);
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
        $uids = Strings::intExplode(',', $uids);
        for ($i = 0, $cnt = count($uids); $i < $cnt; ++$i) {
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
        $data = T3General::_GP('betset');
        if (!is_array($data)) {
            return;
        }
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

                $match = $this->matchRepo->findByUid($matchUid);
                $betUid = key($betArr);
                $betData = current($betArr);

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
            $betUid = key($betData);
            $team = current($betData);
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
