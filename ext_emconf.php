<?php

//#######################################################################
// Extension Manager/Repository config file for ext: "t3sportsbet"
//
// Manual updates:
// Only the data in the array - anything else is removed by next write.
// "version" and "dependencies" must not be touched!
//#######################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'T3sports bet system',
    'description' => 'Bet-system for T3sports. FE-Users can bet on matches in T3sports. Tippspiel auf Basis von T3sports.',
    'category' => 'plugin',
    'author' => 'Rene Nitzsche',
    'author_email' => 'rene@system25.de',
    'author_company' => 'System 25',
    'dependencies' => '',
    'module' => '',
    'version' => '1.0.1',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-11.5.99',
            'rn_base' => '1.16.0-0.0.0',
            'cfc_league' => '1.10.0-0.0.0',
            'cfc_league_fe' => '1.10.0-0.0.0',
            't3users' => '0.4.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
