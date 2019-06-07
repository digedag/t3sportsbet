<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$sysLangFile = tx_rnbase_util_TYPO3::isTYPO87OrHigher() ? 'Resources/Private/Language/locallang_general.xlf' : 'locallang_general.xml';

$tx_t3sportsbet_betsets = Array (
    'ctrl' => Array (
        'title' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets',
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
        'enablecolumns' => [],
        'iconfile' => tx_rnbase_util_Extensions::extRelPath('t3sportsbet').'icon_table.gif',
    ),
    'interface' => Array (
        'showRecordFieldList' => 'hidden,betgame, round, round_name, status'
        ),
    'feInterface' => Array (
        'fe_admin_fieldList' => '',
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
        'teamquestions' => Array (
            'exclude' => 1,
            'label' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_teamquestions',
            'config' => Array (
                'type' => 'inline',
                'foreign_table' => 'tx_t3sportsbet_teamquestions',
                'foreign_field' => 'betset',
                'foreign_sortby' => 'sorting',
                'foreign_label' => 'question',
                'minitems' => 0,
                'maxitems' => 20,
                'appearance' => Array(
                    'collapseAll' => '1',
                    'expandSingle' => '1',
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
        '0' => Array('showitem' => 'hidden;;1;;1-1-1, betgame, round, round_name, status, comment, t3matches, teamquestions')
        ),
    'palettes' => Array (
        '1' => Array('showitem' => '')
    )
);

return $tx_t3sportsbet_betsets;
