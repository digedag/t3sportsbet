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
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, betset, question')
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
		'0' => Array('showitem' => 'question,fe_user,team,finished,possiblepoints,points')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

?>