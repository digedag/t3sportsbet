<?php

namespace Sys25\T3sportsbet\Service;

use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Domain\Repository\FeUserRepository;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\T3sportsbet\Model\Bet;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Utility\ServiceRegistry as BetServiceRegistry;
use System25\T3sports\Model\Fixture;
use System25\T3sports\Utility\ServiceRegistry;
use tx_t3sportsbet_models_betgame;

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

define('T3SPORTSBET_OPEN', 1);
define('T3SPORTSBET_CLOSED', 2);

interface tx_t3sportsbet_DataProvider
{
    public function setBetGame($game);

    public function getRounds($status);

    public function getMatchesByRound($round);
}

/**
 * @author Rene Nitzsche
 */
class BetService
{
    private $feuserRepo;

    public function __construct()
    {
        $this->feuserRepo = new FeUserRepository();
    }

    /**
     * Returns all rounds with open matches.
     *
     * @param tx_t3sportsbet_models_betgame $betgame
     *
     * @return BetSet[]
     */
    public function getOpenRounds(&$betgame)
    {
        return $this->getRounds($betgame, T3SPORTSBET_OPEN);
    }

    /**
     * Returns all rounds with closed matches.
     *
     * @param tx_t3sportsbet_models_betgame $betgame
     *
     * @return BetSet[]
     */
    public function getClosedRounds($betgame)
    {
        return $this->getRounds($betgame, T3SPORTSBET_CLOSED);
    }

    /**
     * Returns an array with all uids of matches within a $betgame.
     *
     * @param tx_t3sportsbet_models_betgame $betgame
     */
    public function findMatchUids($betgame)
    {
        $from = [
            'tx_t3sportsbet_betsets JOIN tx_t3sportsbet_betsets_mm ON tx_t3sportsbet_betsets_mm.uid_local = tx_t3sportsbet_betsets.uid',
            'tx_t3sportsbet_betsets',
        ];
        $options = [];
        $options['where'] = 'tx_t3sportsbet_betsets.betgame = '.intval($betgame->getUid());

        return Connection::getInstance()->doSelect('tx_t3sportsbet_betsets_mm.uid_foreign as uid', $from, $options, 0);
    }

    /**
     * Fill table betsetresults with values for all bets of a betgame.
     *
     * @param tx_t3sportsbet_models_betgame $betGame
     */
    public function updateBetsetResultsByGame(tx_t3sportsbet_models_betgame $betGame)
    {
        // Die Spalte hasresults ist für den Update von älteren Daten vor Einführung der Tabelle
        // tx_t3sportsbet_betsetresults notwendig.
        $betgameWhere = 'tx_t3sportsbet_betsets.betgame='.$betGame->getUid().' AND (tx_t3sportsbet_betsets.status = 1 OR (tx_t3sportsbet_betsets.status = 2 AND tx_t3sportsbet_betsets.hasresults = 0))';

        // Remove old data
        $delWhere = 'tx_t3sportsbet_betsetresults.betset IN('.'SELECT uid FROM tx_t3sportsbet_betsets WHERE '.$betgameWhere.' )';

        Connection::getInstance()->doDelete('tx_t3sportsbet_betsetresults', $delWhere);

        $sqlQuery = '
INSERT INTO tx_t3sportsbet_betsetresults (feuser,points,betset,bets,pid,tstamp,crdate)
SELECT feuser, sum(points), betset, count(bets),'.$betGame->getProperty('pid').', UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM (
  SELECT b.uid, b.fe_user AS feuser, b.points, b.betset, b.uid AS bets
   FROM `tx_t3sportsbet_bets` As b
     JOIN tx_t3sportsbet_betsets ON tx_t3sportsbet_betsets.UID = b.betset
   WHERE '.$betgameWhere.'
  UNION
  SELECT tb.uid, tb.feuser, tb.points, tq.betset, tb.uid AS bets
   FROM `tx_t3sportsbet_teambets` tb
     JOIN tx_t3sportsbet_teamquestions tq ON tb.question = tq.uid
     JOIN tx_t3sportsbet_betsets ON tx_t3sportsbet_betsets.UID = tq.betset
   WHERE '.$betgameWhere.' '.Connection::getInstance()->enableFields('tx_t3sportsbet_teamquestions', 0, 'tq').'
) AS dt
GROUP BY feuser, betset
';
        $ok = Connection::getInstance()->doQuery($sqlQuery);

        // Jetzt die Spalte hasresults füllen
        $values = [
            'hasresults' => 1,
        ];
        Connection::getInstance()->doUpdate('tx_t3sportsbet_betsets', $betgameWhere, $values);

        /*
         *
         *
         * SELECT feuser, sum(points), betset FROM (
         * SELECT b.fe_user AS feuser, b.points, b.betset
         * FROM `tx_t3sportsbet_bets` As b
         * UNION
         * SELECT tb.feuser, tb.points, tq.betset
         * FROM `tx_t3sportsbet_teambets` tb JOIN tx_t3sportsbet_teamquestions tq ON tb.question = tq.uid
         * ) AS dt
         *
         * GROUP BY feuser, betset
         *
         */
    }

    /**
     * Analyze bets of a betgame.
     *
     * @param tx_t3sportsbet_models_betgame $betGame
     *
     * @return int number of finished bets
     */
    public function analyzeBets($betGame)
    {
        // Ablauf
        // Tips ohne Auswertung, deren Spiele beendet sind
        $fields = $options = [];
        $fields['BETSET.BETGAME'][OP_EQ_INT] = $betGame->getUid();
        $fields['BET.FINISHED'][OP_EQ_INT] = 0;
        $fields['MATCH.STATUS'][OP_EQ_INT] = 2;
        // $options['debug'] = 1;
        // This could be memory consuming...
        $bets = $this->searchBet($fields, $options);
        $ret = 0;
        $service = BetServiceRegistry::getCalculatorService();
        for ($i = 0, $cnt = count($bets); $i < $cnt; ++$i) {
            $bet = $bets[$i];
            $values = [
                'finished' => 1,
                'points' => intval($service->calculatePoints($betGame, $bet)),
            ];
            $where = 'uid='.$bet->getUid();
            Connection::getInstance()->doUpdate('tx_t3sportsbet_bets', $where, $values, 0);
            ++$ret;
        }
        // Hook to inform about updated bets
        Misc::callHook('t3sportsbet', 'srv_Bet_analysebets_finished_hook', [
            'calculatedBets' => $ret,
            'betgame' => $betGame,
        ], $this);

        return $ret;
    }

    /**
     * Liefert das höchste und niedrigste Datum von Spielen in einem Tipspiel.
     *
     * @param BetSet $betset
     *
     * @return array|false keys: high and low, values are timestamps or false if no match is set
     */
    public function getBetsetDateRange(BetSet $betset)
    {
        $matches = $betset->getMatches();
        if (!count($matches)) {
            return false;
        }
        $today = time();
        $high = [
            $matches[0]->getDate(),
            $matches[0],
        ];
        $low = [
            $matches[0]->getDate(),
            $matches[0],
        ];
        $next = $matches[0]->getDate() > $today ? [
            $matches[0]->getDate(),
            $matches[0],
        ] : [
            0,
            0,
        ];
        for ($i = 1, $cnt = count($matches); $i < $cnt; ++$i) {
            $match = $matches[$i];
            if ($match->getDate() < $low[0]) {
                $low = [
                    $match->getDate(),
                    $match,
                ];
            }
            if ($match->getDate() >= $high[0]) {
                $high = [
                    $match->getDate(),
                    $match,
                ];
            }
            if ((0 == $next[0] || $match->getDate() < $next[0]) && $match->getDate() > $today) {
                $next = [
                    $match->getDate(),
                    $match,
                ];
            }
        }
        $next = $next[0] > 0 ? $next : 0;

        return [
            'high' => $high,
            'low' => $low,
            'next' => $next,
        ];
    }

    /**
     * Move a match and all bets from one betset to another.
     *
     * @param int $newBetsetUid
     * @param int $oldBetsetUid
     * @param int $matchUid
     *
     * @return int number of bets moved
     */
    public function moveMatch($newBetsetUid, $oldBetsetUid, $matchUid)
    {
        // Zuordnung Spiel im neuen Betset anlegen -> Exception, wenn schon vorhanden
        $newBetSet = tx_rnbase::makeInstance(BetSet::class, $newBetsetUid);
        $matchesInNewBetSet = $this->findMatchUidsByBetSet($newBetSet);
        if (in_array($matchUid, $matchesInNewBetSet)) {
            throw new Exception('Match is already in betset');
        }
        // Zuordnung Spiel im alten Betset entfernen
        $where = 'uid_local='.$oldBetsetUid.' AND uid_foreign='.$matchUid.' AND tablenames=\'tx_cfcleague_games\'';
        $rows = Connection::getInstance()->doUpdate('tx_t3sportsbet_betsets_mm', $where, [
            'uid_local' => $newBetsetUid,
        ]);
        if (0 == $rows) {
            throw new Exception('Match ('.$matchUid.') not found in old betset ('.$oldBetsetUid.')!');
        }
        // Alle Bets auf das neue Betset umstellen
        $where = 'betset='.$oldBetsetUid.' AND t3match='.$matchUid;
        $rows = Connection::getInstance()->doUpdate('tx_t3sportsbet_bets', $where, [
            'betset' => $newBetsetUid,
        ]);

        return $rows;
    }

    /**
     * Return an array with all match uids of a betset.
     *
     * @param BetSet $betset
     */
    public function findMatchUidsByBetSet(BetSet $betset)
    {
        $fields = $options = [];
        $betsetUid = is_object($betset) ? $betset->getUid() : intval($betset);
        $service = ServiceRegistry::getMatchService();
        $fields['BETSETMM.UID_LOCAL'][OP_EQ_INT] = $betsetUid;
        $options['orderby']['BETSETMM.SORTING'] = 'asc';
        $options['what'] = 'uid';
        $result = $service->search($fields, $options);
        $ret = [];
        for ($i = 0, $cnt = count($result); $i < $cnt; ++$i) {
            $ret[] = $result[$i]['uid'];
        }

        return $ret;
    }

    /**
     * Reset bets for a given match on a given betset.
     *
     * @param BetSet $betset
     * @param int $matchUid
     */
    public function resetBets(BetSet $betset, $matchUid)
    {
        // UPDATE tx_t3sportsbet_bets SET finished=0, points=0 WHERE betset = 123 AND t3match=12
        $values = [
            'finished' => 0,
            'points' => 0,
        ];
        $where = 'betset='.$betset->getUid().' AND t3match='.intval($matchUid);
        Connection::getInstance()->doUpdate('tx_t3sportsbet_bets', $where, $values, 0);
    }

    /**
     * Returns the number of bets for a betset.
     *
     * @param BetSet $betset
     */
    public function getBetSize(BetSet $betset)
    {
        $fields = $options = [];
        $fields['BET.BETSET'][OP_EQ_INT] = $betset->getUid();
        $options['count'] = 1;

        return $this->searchBet($fields, $options);
    }

    /**
     * Returns the bet for a user on a single match
     * If no bet is found this method return a dummy instance of Bet
     * with uid=0.
     *
     * @param BetSet $betset
     * @param Fixture $match
     * @param FeUser $feuser
     *
     * @return Bet
     */
    public function getBet(BetSet $betset, $match, $feuser)
    {
        $fields = $options = [];
        $ret = [];
        if ($feuser) {
            // Ohne FE-User kann die DB-Abfragen gespart werden
            $fields['BET.BETSET'][OP_EQ_INT] = $betset->getUid();
            $fields['BET.T3MATCH'][OP_EQ_INT] = $match->getUid();
            $fields['BET.FE_USER'][OP_EQ_INT] = $feuser->getUid();
            // $options['debug'] = 1;
            $ret = $this->searchBet($fields, $options);
        }

        $bet = count($ret) ? $ret[0] : null;
        if (!$bet) {
            // No bet in database found. Create dummy instance
            $bet = tx_rnbase::makeInstance(Bet::class, [
                'uid' => 0,
                'betset' => $betset->getUid(),
                'fe_user' => $feuser ? $feuser->getUid() : null,
                't3match' => $match->getUid(),
            ]);
        }

        return $bet;
    }

    /**
     * Returns all bets on a single match.
     *
     * @param BetSet $betset
     * @param Fixture $match
     *
     * @return Bet[]
     */
    public function getBets(BetSet $betset, $match)
    {
        $fields = $options = [];
        $fields['BET.BETSET'][OP_EQ_INT] = $betset->getUid();
        $fields['BET.T3MATCH'][OP_EQ_INT] = $match->getUid();

        return $this->searchBet($fields, $options);
    }

    /**
     * Returns the bet trend for a single match.
     *
     * @param BetSet $betset
     * @param Fixture $match
     *
     * @return array
     */
    public function getBetTrend(BetSet $betset, $match)
    {
        $fields = $options = [];
        // Wir suchen jeweils die Anzahl der Tips
        $options['count'] = 1;
        $fields['BET.BETSET'][OP_EQ_INT] = $betset->getUid();
        $fields['BET.T3MATCH'][OP_EQ_INT] = $match->getUid();
        $fields[SEARCH_FIELD_CUSTOM] = 'tx_t3sportsbet_bets.goals_home > tx_t3sportsbet_bets.goals_guest';

        $ret = [];
        $ret['trendhome'] = $this->searchBet($fields, $options);
        $fields[SEARCH_FIELD_CUSTOM] = 'tx_t3sportsbet_bets.goals_home = tx_t3sportsbet_bets.goals_guest';
        $ret['trenddraw'] = $this->searchBet($fields, $options);
        $fields[SEARCH_FIELD_CUSTOM] = 'tx_t3sportsbet_bets.goals_home < tx_t3sportsbet_bets.goals_guest';
        $ret['trendguest'] = $this->searchBet($fields, $options);
        $sum = $ret['trendhome'] + $ret['trenddraw'] + $ret['trendguest'];
        $sum = $sum ? $sum : 1;
        $ret['trendhomep'] = round($ret['trendhome'] / $sum * 100);
        $ret['trenddrawp'] = round($ret['trenddraw'] / $sum * 100);
        $ret['trendguestp'] = round($ret['trendguest'] / $sum * 100);

        return $ret;
    }

    /**
     * Returns the bet statistics for a single match.
     *
     * @param BetSet $betset
     * @param Fixture $match
     *
     * @return array
     */
    public function getBetStats(BetSet $betset, $match)
    {
        $fields = $options = [];
        // Wieviele Tipper haben das Ergebnis richtig
        $calcSrv = BetServiceRegistry::getCalculatorService();
        list($goalsHome, $goalsGuest) = $calcSrv->getGoals($betset->getBetgame(), $match);
        // $goalsHome = $match->
        $options['count'] = 1;
        $fields['BET.BETSET'][OP_EQ_INT] = $betset->getUid();
        $fields['BET.T3MATCH'][OP_EQ_INT] = $match->getUid();
        $fields[SEARCH_FIELD_CUSTOM] = 'tx_t3sportsbet_bets.goals_home = '.$goalsHome;
        $fields[SEARCH_FIELD_CUSTOM] .= ' AND tx_t3sportsbet_bets.goals_guest = '.$goalsGuest;
        $ret = [];
        $ret['stats_accurate'] = $this->searchBet($fields, $options);

        return $ret;
    }

    /**
     * Returns the number of users with bets for given bet rounds.
     *
     * @param string $betsetUids comma separated uids of betsets
     *
     * @return int
     */
    public function getResultSize($betsetUids)
    {
        $fields = $options = [];
        if ($betsetUids) {
            $fields['BET.BETSET'][OP_IN_INT] = $betsetUids;
        }
        $fields['BET.FE_USER'][OP_GT_INT] = 0;
        $options['count'] = 1;
        $options['distinct'] = 1;
        // $options['debug'] = 1;
        return $this->feuserRepo->search($fields, $options);
    }

    /**
     * Returns the points and standing for a single user.
     *
     * @param string $betsetUids comma separated uids of betsets
     * @param string $feuserUids comma separated uids of feuserUids to mark
     */
    public function getResults($betsetUids, $feuserUids = '0')
    {
        $fields = $options = [];
        if ('old' == $_GET['resultmode']) {
            if ($betsetUids) {
                $fields['BET.BETSET'][OP_IN_INT] = $betsetUids;
            }
            $fields['BET.FE_USER'][OP_GT_INT] = 0;
            $options['what'] = '
				FEUSER.uid, sum(tx_t3sportsbet_bets.points) AS betpoints,
				sum(tx_t3sportsbet_bets.finished) AS betcount
				';
        } else {
            if ($betsetUids) {
                $fields['BETSETRESULT.BETSET'][OP_IN_INT] = $betsetUids;
            }
            $fields['BETSETRESULT.FEUSER'][OP_GT_INT] = 0;
            $options['what'] = '
			FEUSER.uid, sum(BETSETRESULT.points) AS betpoints,
			sum(BETSETRESULT.bets) AS betcount
			';
        }

        $options['distinct'] = 1;
        $options['orderby']['betpoints'] = 'desc';
        $options['groupby'] = 'FEUSER.uid';
        // $options['debug'] = '1';
        /** @var \Sys25\RnBase\Domain\Collection\BaseCollection $rows */
        $rows = $this->feuserRepo->search($fields, $options);

        $userIds = Strings::intExplode(',', $feuserUids);
        $userIds = array_flip($userIds);
        $userIdx = [];
        $rank = 0;
        $lastPoints = 0;
        $rows = $rows->toArray();
        for ($i = 0, $cnt = count($rows); $i < $cnt; ++$i) {
            $row = &$rows[$i];
            // Check rank
            if ($lastPoints != $row['betpoints']) {
                $rank = $i + 1;
                $lastPoints = $row['betpoints'];
            }
            $row['rank'] = $rank;

            if (array_key_exists($row['uid'], $userIds)) {
                $row['mark'] = 'mark';
                $userIdx[$row['uid']] = $i;
            }
        }

        return [
            0 => $rows,
            1 => $userIdx,
        ];
    }

    public function searchBet($fields, $options)
    {
        $searcher = SearchBase::getInstance('tx_t3sportsbet_search_Bet');

        return $searcher->search($fields, $options);
    }

    public function searchBetSet($fields, $options)
    {
        $searcher = SearchBase::getInstance('tx_t3sportsbet_search_BetSet');

        return $searcher->search($fields, $options);
    }

    public function searchBetSetResult($fields, $options)
    {
        $searcher = SearchBase::getInstance('tx_t3sportsbet_search_BetSetResult');

        return $searcher->search($fields, $options);
    }

    /**
     * Adds new matches to an existing betset.
     *
     * @param BetSet $betset
     * @param array $matchUids commaseparated uids
     *
     * @return string
     */
    public function addMatchesTCE(BetSet $betset, $matchUids)
    {
        $data = [];
        $cnt = count($matchUids);
        $existingUids = $this->getMatchUids($betset);
        $matchUids = array_merge($existingUids, $matchUids);
        $data['tx_t3sportsbet_betsets'][$betset->getUid()]['t3matches'] = 'tx_cfcleague_games_'.implode(',tx_cfcleague_games_', $matchUids);
        $tce = Connection::getInstance()->getTCEmain($data);
        $tce->process_datamap();

        return $cnt;
    }

    private function getMatchUids($betset)
    {
        $matches = $betset->getMatches();
        $ret = [];
        foreach ($matches as $match) {
            $ret[] = $match->getUid();
        }

        return $ret;
    }

    /**
     * @param tx_t3sportsbet_models_betgame $betgame
     * @param int $status
     * @param string $betsetUids commaseperated uids
     *
     * @return BetSet[]
     */
    public function getRounds(&$betgame, $status, $betsetUids = '')
    {
        $fields = [];
        $fields['BETSET.BETGAME'][OP_EQ_INT] = $betgame->getUid();
        if (trim($status)) {
            $fields['BETSET.STATUS'][OP_IN_INT] = $status;
        }
        if (trim($betsetUids)) {
            $fields['BETSET.UID'][OP_IN_INT] = $betsetUids;
        }

        $options = [];
        // $options['debug'] = 1;
        $options['orderby']['BETSET.ROUND'] = 'asc';

        return $this->searchBetSet($fields, $options);
    }

    /**
     * Save or update a bet from fe request.
     *
     * @param BetSet $betset
     * @param Fixture $match
     * @param FeUser $feuser
     * @param int $betUid
     * @param array $betData
     *
     * @return int 0/1 whether the bet was saved or not
     */
    public function saveOrUpdateBet($betset, $match, $feuser, $betUid, $betData)
    {
        if ($match->isRunning() || $match->isFinished()) {
            return 0;
        }
        if ('OPEN' != $betset->getMatchState($match)) {
            return 0;
        }

        if (!(strlen(trim($betData['home'])) || strlen(trim($betData['guest'])))) {
            return 0;
        } // No values given
        // Der Tip muss vom selben User stammen
        $values = [];
        $values['tstamp'] = time();
        $values['goals_home'] = intval($betData['home']);
        $values['goals_guest'] = intval($betData['guest']);
        $betUid = intval($betUid);
        if ($betUid) {
            // Update bet
            $bet = tx_rnbase::makeInstance(Bet::class, $betUid);
            if ($bet->getProperty('fe_user') != $feuser->getUid()) {
                return 0;
            }
            if ($bet->getProperty('goals_home') == $values['goals_home'] && $bet->getProperty('goals_guest') == $values['goals_guest']) {
                return 0;
            }
            $where = 'uid='.$betUid;
            Connection::getInstance()->doUpdate('tx_t3sportsbet_bets', $where, $values, 0);
        } else {
            // Create new bet instance
            // Ein User darf pro Spiel nur einen Tip abgeben
            $bet = $this->getBet($betset, $match, $feuser);
            if ($bet->isPersisted()) {
                return 0;
            } // There is already a bet for this match!

            $values['pid'] = $betset->getProperty('pid');
            $values['crdate'] = $values['tstamp'];
            $values['fe_user'] = $feuser->getUid();
            $values['t3match'] = $match->getUid();
            $values['betset'] = $betset->getUid();
            Connection::getInstance()->doInsert('tx_t3sportsbet_bets', $values, 0);
        }

        return 1;
    }
}
