<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$tx_t3sportsbet_betsets = [
    'ctrl' => [
        'title' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets',
        'label' => 'uid',
        // If label_alt is used, the flexform fails for some reasons... huh??
    //		'label_alt' => 'uid',
        'label_alt' => 'round, round_name',
        'label_alt_force' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:t3sportsbet/icon_table.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,betgame, round, round_name, status',
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
        'betgame' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betgames',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [' ', '0'],
                ],
                'foreign_table' => 'tx_t3sportsbet_betgames',
                'foreign_table_where' => 'AND tx_t3sportsbet_betgames.pid=###CURRENT_PID### ORDER BY tx_t3sportsbet_betgames.sorting ',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'round' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets.round',
            'config' => array(
                'type' => 'input',
                'size' => '4',
                'max' => '4',
                'eval' => 'required,int',
                'range' => [
                    'upper' => '1000',
                    'lower' => '1',
                ],
                'default' => 1,
            ),
        ),
        'round_name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets.round_name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '100',
                'eval' => 'required,trim',
            ],
        ],
        'status' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets.status.prepare', 0],
                    ['LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets.status.open', 1],
                    ['LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets.status.finished', 2],
                ],
                'default' => 0,
            ],
        ],
        't3matches' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betsets.t3matches',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_cfcleague_games',
                'size' => 10,
                'selectedListStyle' => 'width: 450px;',
                'autoSizeMax' => 30,
                'minitems' => 0,
                'maxitems' => 100,
                'MM' => 'tx_t3sportsbet_betsets_mm',
                'MM_match_fields' => [
                    'tablenames' => 'tx_cfcleague_games',
                ],
            ],
        ],
        'teamquestions' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_teamquestions',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_t3sportsbet_teamquestions',
                'foreign_field' => 'betset',
                'foreign_sortby' => 'sorting',
                'foreign_label' => 'question',
                'minitems' => 0,
                'maxitems' => 20,
                'appearance' => [
                    'collapseAll' => '1',
                    'expandSingle' => '1',
                ],
            ],
        ],
        'comment' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_betgames_comment',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'eval' => 'trim',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'hidden;;1;;1-1-1, betgame, round, round_name, status, comment, t3matches, teamquestions'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];

if (\Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher()) {
    unset($tx_t3sportsbet_betsets['interface']['showRecordFieldList']);
}

return $tx_t3sportsbet_betsets;
