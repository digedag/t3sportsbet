<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$tx_t3sportsbet_bets = [
    'ctrl' => [
        'title' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_bets',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'tstamp',
        'delete' => 'deleted',
        'enablecolumns' => [],
        'iconfile' => 'EXT:t3sportsbet/icon_table.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 't3match'
    ],
    'feInterface' => [
        'fe_admin_fieldList' => '',
    ],
    'columns' => [
        'betset' => Array (
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets',
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
            'label' => 'LLL:EXT:cfc_league/Resources/Private/Language/locallang_db.xml:tx_cfcleague_games',
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
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_bets.finished',
            'config' => Array (
                'type' => 'check',
                'default' => '0'
            )
        ),
        'goals_home' => Array (
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_bets.goals_home',
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
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_bets.goals_guest',
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
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_bets.points',
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
    ],
    'types' => [
        '0' => ['showitem' => 'betset,fe_user,t3match,finished,goals_home,goals_guest,points']
    ],
    'palettes' => [
        '1' => ['showitem' => '']
    ]
];

if (\Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher()) {
    unset($tx_t3sportsbet_bets['interface']['showRecordFieldList']);
}

return $tx_t3sportsbet_bets;
