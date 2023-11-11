<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

$sysLangFile = 'Resources/Private/Language/locallang_general.xlf';

$tx_t3sportsbet_tq = [
    'ctrl' => [
        'title' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_teamquestions',
        'label' => 'question',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'tstamp',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:t3sportsbet/Resources/Public/Icons/icon_table.gif',
    ],
    'interface' => [
        'showRecordFieldList' => '',
    ],
    'feInterface' => [
        'fe_admin_fieldList' => '',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => \Sys25\RnBase\Backend\Utility\TcaTool::buildGeneralLabel('hidden'),
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'betset' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betsets',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_t3sportsbet_betsets',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'question' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_teamquestions_question',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'eval' => 'required,trim',
            ],
        ],
        'openuntil' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_teamquestions_openuntil',
            'config' => [
                'type' => 'input',
                'size' => '12',
                'max' => '20',
                'eval' => 'datetime',
                'default' => '0',
                'checkbox' => '0',
            ],
        ],
        'points' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_teamquestions_points',
            'config' => [
                'type' => 'input',
                'size' => '4',
                'max' => '4',
                'eval' => 'int',
                'range' => [
                    'upper' => '100',
                    'lower' => '0',
                ],
                'default' => 0,
            ],
        ],
        'teams' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_teamquestions_teams',
            'config' => [
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
                'MM_match_fields' => [
                    'tablenames' => 'tx_cfcleague_teams',
                ],
            ],
        ],
        'team' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_teamquestions_team',
            'config' => [
                'type' => 'select',
                'itemsProcFunc' => 'Sys25\T3sportsbet\Utility\ItemFunctions->getTeams4TeamBet',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 10,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'hidden;;1;;1-1-1, betset, question, openuntil, points, teams, team'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];

if (\Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher()) {
    unset($tx_t3sportsbet_tq['interface']['showRecordFieldList']);
}

return $tx_t3sportsbet_tq;
