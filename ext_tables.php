<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add plugin wizard
if (TYPO3_MODE == 'BE') {
    ////////////////////////////////
    // Submodul anmelden
    ////////////////////////////////
    $modName = 'web_CfcLeagueM1';

    tx_rnbase_util_Extensions::insertModuleFunction(
        $modName,
        \Sys25\T3sportsbet\Module\Controller\BetGame::class,
        '',
        'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xml:tx_t3sportsbet_module.name'
    );
}
