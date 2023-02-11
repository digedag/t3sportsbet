<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

$tx_t3sportsbet_betgame = [
    'ctrl' => [
        'title' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'fe_group' => 'fe_group',
        ],
        'iconfile' => 'EXT:t3sportsbet/icon_table.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,starttime,fe_group,name',
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'hidden, starttime, name',
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
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0',
                ],
            ],
        'fe_group' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0],
                    ['LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2],
                    ['LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--'],
                    ],
                'foreign_table' => 'fe_groups',
                ],
            ],
        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
                ],
            ],
        'competition' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:cfc_league/Resources/Private/Language/locallang_db.xlf:tx_cfcleague_games.competition',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_cfcleague_competition',
                'size' => 5,
                'autoSizeMax' => 30,
                'minitems' => 0,
                'maxitems' => 99,
                ],
            ],
        'points_accurate' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.points_accurate',
            'config' => [
                'type' => 'input',
                'size' => '4',
                'max' => '4',
                'eval' => 'int,required',
                'range' => [
                    'upper' => '100',
                    'lower' => '0',
                    ],
                'default' => 0,
                ],
            ],
        'points_goalsdiff' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.points_goalsdiff',
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
        'points_tendency' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.points_tendency',
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
        'draw_if_extratime' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.draw_if_extratime',
            'config' => [
                'type' => 'check',
                'default' => 0,
                ],
            ],
        'draw_if_penalty' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.draw_if_penalty',
            'config' => [
                'type' => 'check',
                'default' => 0,
                ],
            ],
        'lockminutes' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.lockminutes',
            'config' => [
                'type' => 'input',
                'size' => '4',
                'max' => '900',
                'eval' => 'int,required',
                'range' => [
                    'upper' => '900',
                    'lower' => '0',
                ],
                'default' => 30,
            ],
        ],
        'comment' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames_comment',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'eval' => 'trim',
            ],
        ],
            //		'ignore_greentable' => Array (
                //			'exclude' => 0,
                //			'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betgames.ignore_greentable',
                //			'config' => Array (
                    //				'type' => 'check',
                //				'default' => 1
                //			)
            //		),
    ],
    'types' => [
        '0' => ['showitem' => 'hidden;;1;;1-1-1, name, dataprovider, competition, points_accurate, points_goalsdiff, points_tendency, draw_if_extratime, draw_if_penalty, lockminutes, comment'],
    ],
    'palettes' => [
        '1' => ['showitem' => 'starttime, fe_group'],
    ],
];

if (\Sys25\RnBase\Utility\TYPO3::isTYPO104OrHigher()) {
    unset($tx_t3sportsbet_betgame['interface']['showRecordFieldList']);
}

return $tx_t3sportsbet_betgame;
