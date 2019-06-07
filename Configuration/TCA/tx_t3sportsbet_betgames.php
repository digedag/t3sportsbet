<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$sysLangFile = tx_rnbase_util_TYPO3::isTYPO87OrHigher() ? 'Resources/Private/Language/locallang_general.xlf' : 'locallang_general.xml';

$tx_t3sportsbet_betgame = Array (
    'ctrl' => Array (
        'title' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => Array (
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'fe_group' => 'fe_group',
        ),
        'iconfile' => tx_rnbase_util_Extensions::extRelPath('t3sportsbet').'icon_table.gif',
    ),
    'interface' => Array (
        'showRecordFieldList' => 'hidden,starttime,fe_group,name'
    ),
    'feInterface' => Array (
        'fe_admin_fieldList' => 'hidden, starttime, name',
    ),
    'columns' => Array (
        'hidden' => Array (
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/'.$sysLangFile.':LGL.hidden',
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

return $tx_t3sportsbet_betgame;
