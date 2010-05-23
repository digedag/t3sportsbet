<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$TCA['tx_t3sportsbet_betgames'] = Array (
	'ctrl' => $TCA['tx_t3sportsbet_betgames']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,fe_group,name'
	),
	'feInterface' => $TCA['tx_t3sportsbet_betgames']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'fe_group' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					Array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'name' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'competition' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cfc_league/locallang_db.xml:tx_cfcleague_games.competition',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_cfcleague_competition',
				'size' => 5,
				'autoSizeMax' => 30,
				'minitems' => 0,
				'maxitems' => 99,
			)
		),
		'points_accurate' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.points_accurate',
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int,required',
				'range' => Array (
					'upper' => '100',
					'lower' => '0'
				),
				'default' => 0
			)
		),
		'points_goalsdiff' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.points_goalsdiff',
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
		'points_tendency' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.points_tendency',
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
		'draw_if_extratime' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.draw_if_extratime',
			'config' => Array (
				'type' => 'check',
				'default' => 0
			)
		),
		'draw_if_penalty' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.draw_if_penalty',
			'config' => Array (
				'type' => 'check',
				'default' => 0
			)
		),
		'lockminutes' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.lockminutes',
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '900',
				'eval' => 'int,required',
				'range' => Array (
					'upper' => '900',
					'lower' => '0'
				),
				'default' => 30
			)
		),
		'comment' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames_comment',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
				'eval' => 'trim',
			)
		),
//		'ignore_greentable' => Array (
//			'exclude' => 0,
//			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames.ignore_greentable',
//			'config' => Array (
//				'type' => 'check',
//				'default' => 1
//			)
//		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, name, dataprovider, competition, points_accurate, points_goalsdiff, points_tendency, draw_if_extratime, draw_if_penalty, lockminutes, comment')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime, fe_group')
	)
);

$TCA['tx_t3sportsbet_betsets'] = Array (
	'ctrl' => $TCA['tx_t3sportsbet_betsets']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,betgame, round, round_name, status'
	),
	'feInterface' => $TCA['tx_t3sportsbet_betsets']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'betgame' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array(' ', '0'),
				),
		    'foreign_table' => 'tx_t3sportsbet_betgames',
				'foreign_table_where' => 'AND tx_t3sportsbet_betgames.pid=###CURRENT_PID### ORDER BY tx_t3sportsbet_betgames.sorting ',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'round' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets.round',
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'required,int',
				'range' => Array (
					'upper' => '1000',
					'lower' => '1'
				),
				'default' => 1
			)
		),
		'round_name' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets.round_name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '100',
				'eval' => 'required,trim',
			)
		),
		'status' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets.status',
			'config' => Array (
				'type' => 'select',
				'items' => Array(
					Array('LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets.status.prepare',0),
					Array('LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets.status.open',1),
					Array('LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets.status.finished',2),
				),
				'default' => 0
			)
		),
		't3matches' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets.t3matches',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_cfcleague_games',
				'size' => 10,
				'selectedListStyle' => 'width: 450px;',
				'autoSizeMax' => 30,
				'minitems' => 0,
				'maxitems' => 100,
				'MM' => 'tx_t3sportsbet_betsets_mm',
				'MM_match_fields' => Array(
					'tablenames' => 'tx_cfcleague_games',
				),
			)
		),
		'comment' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames_comment',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
				'eval' => 'trim',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, betgame, round, round_name, status, comment, t3matches')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

$TCA['tx_t3sportsbet_bets'] = Array (
	'ctrl' => $TCA['tx_t3sportsbet_bets']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 't3match'
	),
	'feInterface' => $TCA['tx_t3sportsbet_bets']['feInterface'],
	'columns' => Array (
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
		'fe_user' => Array (
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
		't3match' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cfc_league/locallang_db.xml:tx_cfcleague_games',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_cfcleague_games',
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
		'goals_home' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_bets.goals_home',		
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
		'goals_guest' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_bets.goals_guest',		
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
		'points' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_bets.points',		
			'config' => Array (
				'type' => 'input',
				'size' => '4',
				'max' => '4',
				'eval' => 'int',
//				'checkbox' => '0',
				'range' => Array (
					'upper' => '1000',
					'lower' => '0'
				),
				'default' => 0
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'betset,fe_user,t3match,finished,goals_home,goals_guest,points')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

?>