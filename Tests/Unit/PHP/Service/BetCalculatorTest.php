<?php

namespace System25\T3sportsbet\Tests\Service;

/***************************************************************
*  Copyright notice
*
*  (c) 2008-2020 Rene Nitzsche (rene@system25.de)
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

class BetCalculatorTest extends \tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testBetCalculation()
    {
        // betgame
        $betgame = $this->getBetgame(5, 3, 1);

        // bet
        $bet = $this->getBet(2, 1);
        // srv
        $calculator = \tx_rnbase::makeInstance('tx_t3sportsbet_services_betcalculator');
        // Matches
        $matches = $this->getMatches();

        $bet->setProperty('t3match', $matches['match_2_0']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);

        $this->assertEquals(1, $result, 'Match 2:0 Bet 2:1 Points: '.$result);

        $bet->setProperty('t3match', $matches['match_1_1']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(0, $result, 'Match 1:1 Bet 2:1 Points: '.$result);

        $bet->setProperty('t3match', $matches['match_1_0']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(3, $result, 'Match 1:0 Bet 2:1 Points: '.$result);

        $bet->setProperty('t3match', $matches['match_1_2']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(0, $result, 'Match 1:2 Bet 2:1 Points: '.$result);

        $bet = $this->getBet(3, 0);
        $bet->setProperty('t3match', $matches['match_3_0']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(5, $result, 'Match 3:0 Bet 3:0 Points: '.$result);
    }

    /**
     * @group unit
     */
    public function testBetCalculationCup()
    {
        $betgame = $this->getBetgame(5, 0, 1, 1);
        $betgame2 = $this->getBetgame(5, 0, 1, 0);
        $betgame3 = $this->getBetgame(5, 2, 1, 0, 1);
        $calculator = \tx_rnbase::makeInstance('tx_t3sportsbet_services_betcalculator');
        $matches = $this->getMatches();
        $bet = $this->getBet(3, 1);
        $bet->setProperty('t3match', $matches['match_3_1_et']->getUid());
        // 3:1 n.V., Da Unentschieden bei Verlängerung aktiviert ist, gibt es keinen Punkt
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(0, $result, 'Match 3:1 et (1:1) Bet 3:1 Points: '.$result);
        $result = $calculator->calculatePoints($betgame2, $bet);
        $this->assertEquals(5, $result, 'Match 3:1 et (1:1) Bet 3:1 Points: '.$result);

        $bet = $this->getBet(2, 2);
        $bet->setProperty('t3match', $matches['match_3_1_et']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(1, $result, 'Match 3:1 et (1:1) Bet 2:2 Points: '.$result);
        $result = $calculator->calculatePoints($betgame2, $bet);
        $this->assertEquals(0, $result, 'Match 3:1 et (1:1) Bet 3:1 Points: '.$result);

        $bet = $this->getBet(1, 1);
        $bet->setProperty('t3match', $matches['match_3_1_et']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(5, $result, 'Match 3:1 et (1:1) Bet 1:1 Points: '.$result);

        $bet = $this->getBet(2, 2);
        $bet->setProperty('t3match', $matches['match_7_6_ap']->getUid());
        $result = $calculator->calculatePoints($betgame3, $bet);
        $this->assertEquals(2, $result, 'Match 7:6 et (4:4, 3:3) Bet 2:2 Points: '.$result);
    }

    /**
     * @group unit
     */
    public function testBetCalculationWoDiff()
    {
        // Ohne Tordiff testen
        $betgame = $this->getBetgame(5, 0, 1);
        $bet = $this->getBet(2, 1);
        $calculator = \tx_rnbase::makeInstance('tx_t3sportsbet_services_betcalculator');
        $matches = $this->getMatches();

        $bet->setProperty('t3match', $matches['match_1_0']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(1, $result, 'Match 1:0 Bet 2:1 Points: '.$result);

        $bet->setProperty('t3match', $matches['match_1_2']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(0, $result, 'Match 1:2 Bet 2:1 Points: '.$result);

        $bet = $this->getBet(3, 0);
        $bet->setProperty('t3match', $matches['match_3_0']->getUid());
        $result = $calculator->calculatePoints($betgame, $bet);
        $this->assertEquals(5, $result, 'Match 3:0 Bet 3:0 Points: '.$result);
    }

    /**
     * @group unit
     */
    public function testGetGoals()
    {
        $betgame3 = $this->getBetgame(5, 2, 1, 0, 1);
        $calculator = \tx_rnbase::makeInstance('tx_t3sportsbet_services_betcalculator');
        $matches = $this->getMatches();
        list($goalsHome, $goalsGuest) = $calculator->getGoals($betgame3, $matches['match_7_6_ap']);
        $this->assertEquals('4:4', $goalsHome.':'.$goalsGuest, '7:6 n.E. (3:3, 4:4) Tipspiel (Unentschieden nach Elfm.) sollte 4:4 sein');
    }

    private function getBetgame($p1, $p2, $p3, $drawIfET = 0, $drawIfPenalty = 0)
    {
        $record = [];
        $record['uid'] = 1;
        $record['points_accurate'] = $p1;
        $record['points_goalsdiff'] = $p2;
        $record['points_tendency'] = $p3;
        $record['draw_if_extratime'] = $drawIfET;
        $record['draw_if_penalty'] = $drawIfPenalty;

        return \tx_rnbase::makeInstance('tx_t3sportsbet_models_betgame', $record);
    }

    private function getBet($home, $guest)
    {
        $record = [];
        $record['uid'] = 1;
        $record['goals_home'] = $home;
        $record['goals_guest'] = $guest;

        return \tx_rnbase::makeInstance('tx_t3sportsbet_models_bet', $record);
    }

    private function getMatches()
    {
        $data = \tx_rnbase_util_Spyc::YAMLLoad($this->getFixturePath('util_Matches.yaml'));
        $data = $data['matches'];
        $matches = $this->makeInstances($data, $data['clazz']);
        foreach ($matches as $match) {
            // Die Spiele in das Instance-Array legen
            \tx_cfcleaguefe_models_match::addInstance($match);
        }
        reset($matches);

        return $matches;
    }

    private function makeInstances($yamlData, $clazzName)
    {
        // Sicherstellen, daß die Klasse geladen wurde
        $competition = \tx_rnbase::makeInstance(
            'tx_cfcleague_models_Competition',
            [
                'uid' => 1,
                'name' => 'dummy',
                'match_parts' => 2,
                'addparts' => 0,
            ]
        );
        foreach ($yamlData as $key => $arr) {
            if (isset($arr['record']) && is_array($arr['record'])) {
                $mock = $this->getMockBuilder($clazzName)
                    ->disableOriginalConstructor()
                    ->setMethods(['getCompetition'])
                    ->getMock();
                $mock->expects($this->any())->method('getCompetition')
                    ->will($this->returnValue($competition));
                $mock->__construct($arr['record']);
                $ret[$key] = $mock;
            }
        }

        return $ret;
    }

    public function getFixturePath($filename)
    {
        return \tx_rnbase_util_Extensions::extPath('t3sportsbet').'Tests/Fixtures/'.$filename;
    }
}
