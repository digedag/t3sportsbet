<?php

// #######################################################################
// Extension Manager/Repository config file for ext: "t3sportsbet"
//
// Manual updates:
// Only the data in the array - anything else is removed by next write.
// "version" and "dependencies" must not be touched!
// #######################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'T3sports bet system',
    'description' => 'Bet-system for T3sports. FE-Users can bet on matches in T3sports. Tippspiel auf Basis von T3sports.',
    'category' => 'plugin',
    'author' => 'Rene Nitzsche',
    'author_email' => 'rene@system25.de',
    'author_company' => 'System 25',
    'dependencies' => '',
    'module' => '',
    'version' => '1.3.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-12.4.99',
            'rn_base' => '1.18.0-0.0.0',
            'cfc_league' => '1.11.0-0.0.0',
            'cfc_league_fe' => '1.11.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
