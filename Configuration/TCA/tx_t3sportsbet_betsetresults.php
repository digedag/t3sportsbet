<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

$tx_t3sportsbet_betsetresults = [
    'ctrl' => [
        'title' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_betsetresults',
        'label' => 'uid',
        'label_alt' => 'betset, feuser',
        'label_alt_force' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'crdate desc',
        'delete' => 'deleted',
        'enablecolumns' => [],
        'iconfile' => 'EXT:t3sportsbet/Resources/Public/Icons/icon_table.gif',
    ],
    'interface' => [
    ],
    'feInterface' => [
        'fe_admin_fieldList' => '',
    ],
    'columns' => [
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
        'feuser' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.feUser',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'points' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_bets.points',
            'config' => [
                'type' => 'input',
                'size' => '4',
                'max' => '4',
                'eval' => 'int',
                'range' => [
                    'upper' => '1000',
                    'lower' => '0',
                ],
                'default' => 0,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'betset,feuser,points'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];

return $tx_t3sportsbet_betsetresults;
