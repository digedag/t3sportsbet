<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$TCA['tx_t3sportsbet_teamquestions'] = Array (
	'ctrl' => $TCA['tx_t3sportsbet_teamquestions']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => ''
	),
	'feInterface' => $TCA['tx_t3sportsbet_teamquestions']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'betset' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_t3sportsbet_betsets',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'question' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teamquestions_question',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
				'eval' => 'required,trim',
			)
		),
		'openuntil' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teamquestions_openuntil',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'points' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teamquestions_points',
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'range' => Array (
					'upper' => '100',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'teams' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teamquestions_teams',		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_cfcleague_teams',
				'foreign_table_where' => 'AND tx_cfcleague_teams.uid IN (
SELECT distinct t.uid
FROM tx_cfcleague_teams t 
  JOIN tx_cfcleague_competition c ON FIND_IN_SET(t.uid, c.teams)
  JOIN tx_t3sportsbet_betgames g ON FIND_IN_SET(c.uid, g.competition)
  JOIN tx_t3sportsbet_betsets s ON s.betgame = g.uid
  JOIN tx_t3sportsbet_teamquestions q ON q.betset = s.uid
  WHERE q.uid = ###THIS_UID###
)
				',
				'size' => 10,
				'autoSizeMax' => 50,
				'minitems' => 0,
				'maxitems' => 100,
				'MM' => 'tx_t3sportsbet_teamquestions_mm',
				'MM_match_fields' => Array (
					'tablenames' => 'tx_cfcleague_teams',
				),
			),
		),
		'team' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teamquestions_team',
			'config' => Array (
				'type' => 'select',
//				'items' => Array (
//					Array(' ', '0'),
//				),
				'itemsProcFunc' => 'tx_t3sportsbet_util_ItemFunctions->getTeams4TeamBet',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, betset, question, openuntil, points, teams, team')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

$TCA['tx_t3sportsbet_teambets'] = Array (
	'ctrl' => $TCA['tx_t3sportsbet_teambets']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 't3match'
	),
	'feInterface' => $TCA['tx_t3sportsbet_teambets']['feInterface'],
	'columns' => Array (
		'question' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teamquestions',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_t3sportsbet_teamquestions',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'feuser' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),

		'finished' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_bets.finished',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'team' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_bets.goals_home',		
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_cfcleague_teams',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'points' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_bets.points',		
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'range' => Array (
					'upper' => '1000',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'possiblepoints' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teambets_possiblepoints',		
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
				'range' => Array (
					'upper' => '1000',
					'lower' => '0'
				),
				'default' => 0
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'question,feuser,team,finished,possiblepoints,points')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

?>