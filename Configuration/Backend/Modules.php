<?php

return [
    'web_CfcLeagueM1_betgame' => [
        'parent' => 'web_CfcLeagueM1',
        'access' => 'user',
        'workspaces' => '*',
        'iconIdentifier' => 'ext-cfcleague-ext-default',
        'path' => '/module/web/t3sports/betgame',
        'labels' => [
            'title' => 'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_module.name',
        ],
        'routes' => [
            '_default' => [
                'target' => \Sys25\T3sportsbet\Module\Controller\BetGame::class.'::main',
            ],
        ],
        'moduleData' => [
            'langFiles' => [],
            'pages' => '0',
            'depth' => 0,
        ],
    ],
];
