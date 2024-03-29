<?php

namespace Sys25\T3sportsbet\Service;

use Sys25\RnBase\Cache\CacheManager;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Domain\Repository\FeUserRepository;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Strings;
use Sys25\T3sportsbet\Model\BetGame;
use Sys25\T3sportsbet\Model\TeamBet;
use Sys25\T3sportsbet\Model\TeamQuestion;
use Sys25\T3sportsbet\Search\TeamBetSearch;
use Sys25\T3sportsbet\Search\TeamQuestionSearch;
use System25\T3sports\Utility\ServiceRegistry;
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
 * @author Rene Nitzsche
 */
class TeamBetService
{
    private $feuserRepo;

    public function __construct()
    {
        $this->feuserRepo = new FeUserRepository();
    }

    /**
     * Analyze teambets of a betgame.
     *
     * @param BetGame $betGame
     *
     * @return int number of finished bets
     */
    public function analyzeBets(BetGame $betGame)
    {
        $fields = $options = [];
        // Ablauf
        // Tips ohne Auswertung, deren Spiele beendet sind
        $fields['BETSET.BETGAME'][OP_EQ_INT] = $betGame->getUid();
        $fields['TEAMBET.FINISHED'][OP_EQ_INT] = 0;
        $fields['TEAMQUESTION.TEAM'][OP_GT_INT] = 0; // Team ist gesetzt

        // $options['debug'] = 1;
        // This could be memory consuming...
        $bets = $this->searchTeamBet($fields, $options);
        // Für jeden Tip die TeamQuestion holen. Danach die Teams vergleichen
        $ret = 0;
        for ($i = 0, $cnt = count($bets); $i < $cnt; ++$i) {
            $bet = $bets[$i];
            $question = $this->loadTeamQuestion($bet->getTeamQuestionUid());
            $values = [];
            $values['finished'] = 1;
            $values['points'] = $question->isWinningBet($bet) ? $bet->getProperty('possiblepoints') : 0;
            $where = 'uid='.$bet->getUid();
            Connection::getInstance()->doUpdate('tx_t3sportsbet_teambets', $where, $values, 0);
            ++$ret;
        }

        return $ret;
    }

    /**
     * Reset all bets for team questions.
     *
     * @param mixed $teamQuestionUids commaseparated uids of team questions
     *
     * @return int number of bets
     */
    public function resetTeamBets($teamQuestionUids)
    {
        $teamQuestionUids = implode(',', Strings::intExplode(',', $teamQuestionUids));
        if (!$teamQuestionUids) {
            return 0;
        }
        $values = [];
        $values['finished'] = 0;
        $values['points'] = 0;
        $where = 'question IN ('.$teamQuestionUids.')';

        return Connection::getInstance()->doUpdate('tx_t3sportsbet_teambets', $where, $values);
    }

    /**
     * Load a teamquestion from database.
     * This access is cached.
     *
     * @param int $uid
     *
     * @return TeamQuestion
     */
    public function loadTeamQuestion($uid)
    {
        $cache = CacheManager::getCache('t3sports');
        $question = $cache->get('t3sbet_tq_'.$uid);
        if (!$question) {
            $question = tx_rnbase::makeInstance(TeamQuestion::class, $uid);
            $cache->set('t3sbet_tq_'.$uid, $question);
        }

        return $question;
    }

    /**
     * Returns the number of bets for a teamquestion.
     *
     * @param TeamQuestion $teamQuestion
     *
     * @return int
     */
    public function getBetCount($teamQuestion)
    {
        $fields = $options = [];
        $fields['TEAMBET.QUESTION'][OP_EQ_INT] = $teamQuestion->getUid();
        $options['count'] = 1;

        return $this->searchTeamBet($fields, $options);
    }

    /**
     * Returns the teambet for a user
     * If no bet is found this method return a dummy instance of TeamBet
     * with uid=0.
     *
     * @param TeamQuestion $teamQuestion
     * @param FeUser $feuser
     *
     * @return TeamBet
     */
    public function getTeamBet($teamQuestion, $feuser)
    {
        $fields = $options = [];
        $ret = [];
        if ($feuser) {
            // Ohne FE-User kann die DB-Abfragen gespart werden
            $fields['TEAMBET.QUESTION'][OP_EQ_INT] = $teamQuestion->getUid();
            $fields['TEAMBET.FEUSER'][OP_EQ_INT] = $feuser->getUid();
            // $options['debug'] = 1;
            $ret = $this->searchTeamBet($fields, $options);
        }

        $bet = count($ret) ? $ret[0] : null;
        if (!$bet) {
            // No bet in database found. Create dummy instance
            $bet = tx_rnbase::makeInstance(TeamBet::class, [
                'uid' => 0,
                'question' => $teamQuestion->getUid(),
                'fe_user' => $feuser ? $feuser->getUid() : 0,
            ]);
        }

        return $bet;
    }

    /**
     * Is a teambet possible for a user.
     *
     * @param TeamQuestion $teamQuestion
     * @param FeUser $feuser
     */
    public function getTeamQuestionStatus(TeamQuestion $teamQuestion, $feuser)
    {
        $state = 'CLOSED';
        if ($feuser) {
            $state = $teamQuestion->isOpen() ? 'OPEN' : $state;
            if ('OPEN' == $state) {
                // Prüfen, ob der aktuelle User seinen eigenen Tip bearbeiten will
                $currUser = $this->feuserRepo->getCurrent();
                if (!($currUser && $currUser->getUid() == $feuser->getUid())) {
                    $state = 'CLOSED';
                }
            }
        }

        return $state;
    }

    /**
     * Load all possible teams for a given teamquestion.
     *
     * @param TeamQuestion $teamQuestion
     */
    public function getTeams4TeamQuestion(TeamQuestion $teamQuestion)
    {
        $srv = ServiceRegistry::getTeamService();
        $fields = [];
        $fields['TEAMQUESTIONMM.UID_LOCAL'][OP_EQ_INT] = $teamQuestion->getUid();
        $fields['TEAMQUESTIONMM.TABLENAMES'][OP_EQ] = 'tx_cfcleague_teams';

        $options = [];
        $options['orderby']['TEAMQUESTIONMM.SORTING'] = 'asc';

        return $srv->searchTeams($fields, $options);
    }

    /**
     * Load all teams for a given betgame.
     *
     * @param BetGame $betgame
     */
    public function getTeams4Betgame(BetGame $betgame)
    {
        // $betgame->getCompetitions();
        // Search for teams
        $fields = [];
        $fields['COMPETITION.UID'][OP_IN_INT] = $betgame->getProperty('competition');
        $options = [];
        $options['distinct'] = 1;
        $options['orderby']['TEAM.NAME'] = 'asc';
        $srv = ServiceRegistry::getTeamService();

        return $srv->searchTeams($fields, $options);
    }

    /**
     * Returns the bet trend for a single teambet.
     *
     * @param TeamQuestion $teamQuestion
     *
     * @return array
     */
    public function getBetTrend(TeamQuestion $teamQuestion)
    {
        // Wir suchen jeweils die Anzahl der Tips pro Team
        // SELECT count(team), team FROM `tx_t3sportsbet_teambets` WHERE question=1 GROUP BY team
        $fields = [];
        $fields['TEAMBET.QUESTION'][OP_EQ_INT] = $teamQuestion->getUid();
        $options = [];
        $options['what'] = 'count(team) AS betcount, team';
        $options['groupby'] = 'team';
        $options['orderby']['betcount'] = 'desc';
        $ret = $this->searchTeamBet($fields, $options);
        $sum = 0;
        foreach ($ret as $row) {
            $sum += $row['betcount'];
        }
        for ($i = 0, $cnt = count($ret); $i < $cnt; ++$i) {
            $ret[$i]['betcountp'] = round($ret[$i]['betcount'] / $sum * 100);
        }

        return $ret;
    }

    /**
     * Save or update a teambet from fe request.
     *
     * @param TeamQuestion $teamQuestion
     * @param FeUser $feuser
     * @param int $betUid
     * @param int $teamUid
     *
     * @return int 0/1 whether the bet was saved or not
     */
    public function saveOrUpdateBet(TeamQuestion $teamQuestion, $feuser, $betUid, $teamUid)
    {
        $betset = $teamQuestion->getBetSet();
        if (!$teamQuestion->isOpen()) {
            return 0;
        }
        if ($betset->isFinished()) {
            return 0;
        }

        $teamUid = intval($teamUid);
        if (!$teamUid) {
            return 0; // No values given
        }
        // Der Tip muss vom selben User stammen
        $values = [];
        $values['tstamp'] = time();
        $values['team'] = $teamUid;
        $values['possiblepoints'] = $teamQuestion->getPoints();
        $betUid = intval($betUid);
        if ($betUid) {
            // Update bet
            $bet = tx_rnbase::makeInstance(TeamBet::class, $betUid);
            if ($bet->getProperty('feuser') != $feuser->getUid()) {
                return 0;
            }
            if ($bet->getProperty('team') == $values['team']) {
                return 0;
            }
            $where = 'uid='.$betUid;
            Connection::getInstance()->doUpdate('tx_t3sportsbet_teambets', $where, $values, 0);
        } else {
            // Create new teambet instance
            // Ein User darf pro Frage nur einen Tip abgeben
            $bet = $this->getTeamBet($teamQuestion, $feuser);
            if ($bet->isPersisted()) {
                return 0;
            } // There is already a bet for this match!

            $values['pid'] = $teamQuestion->getProperty('pid');
            $values['crdate'] = $values['tstamp'];
            $values['feuser'] = $feuser->getUid();
            $values['question'] = $teamQuestion->getUid();
            Connection::getInstance()->doInsert('tx_t3sportsbet_teambets', $values, 0);
        }

        return 1;
    }

    public function searchTeamQuestion($fields, $options)
    {
        $searcher = SearchBase::getInstance(TeamQuestionSearch::class);

        return $searcher->search($fields, $options);
    }

    public function searchTeamBet($fields, $options)
    {
        $searcher = SearchBase::getInstance(TeamBetSearch::class);

        return $searcher->search($fields, $options);
    }
}
