<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

$_EXTKEY = 't3sportsbet';

// Hook for match search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Match_getTableMapping_hook'][] = \Sys25\T3sportsbet\Hook\SearchHook::class.'->getTableMappingMatch';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Match_getJoins_hook'][] = \Sys25\T3sportsbet\Hook\SearchHook::class.'->getJoinsMatch';

// Hook for team search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Team_getTableMapping_hook'][] = \Sys25\T3sportsbet\Hook\SearchHook::class.'->getTableMappingTeam';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Team_getJoins_hook'][] = \Sys25\T3sportsbet\Hook\SearchHook::class.'->getJoinsTeam';

// Hook for feuser search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['search_FeUser_getTableMapping_hook'][] = \Sys25\T3sportsbet\Hook\SearchFeuserHook::class.'->getTableMapping';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['search_FeUser_getJoins_hook'][] = \Sys25\T3sportsbet\Hook\SearchFeuserHook::class.'->getJoins';

// Hook for team marker
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league_fe']['teamMarker_initRecord'][] = \Sys25\T3sportsbet\Hook\MarkerHook::class.'->initTeam';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \Sys25\T3sportsbet\Hook\TCEHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = \Sys25\T3sportsbet\Hook\TCEHook::class;

// \Sys25\RnBase\Utility\Extensions::addService(
//     $_EXTKEY,
//     't3sportsbet' /* sv type */ ,
//     'tx_t3sportsbet_services_bet' /* sv key */ ,
//     [
//       'title' => 'Bet game', 'description' => 'Working with bet games', 'subtype' => 'bet',
//       'available' => true, 'priority' => 50, 'quality' => 50,
//       'os' => '', 'exec' => '',
//       'classFile' => \Sys25\RnBase\Utility\Extensions::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_bet.php',
//       'className' => 'tx_t3sportsbet_services_bet',
//     ]
// );

// \Sys25\RnBase\Utility\Extensions::addService(
//     $_EXTKEY,
//     't3sportsbet' /* sv type */ ,
//     'tx_t3sportsbet_services_teambet' /* sv key */ ,
//     [
//       'title' => 'Teambets', 'description' => 'Working with team bets', 'subtype' => 'teambet',
//       'available' => true, 'priority' => 50, 'quality' => 50,
//       'os' => '', 'exec' => '',
//       'classFile' => \Sys25\RnBase\Utility\Extensions::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_teambet.php',
//       'className' => 'tx_t3sportsbet_services_teambet',
//     ]
// );

// \Sys25\RnBase\Utility\Extensions::addService(
//     $_EXTKEY,
//     't3sportsbet' /* sv type */ ,
//     'tx_t3sportsbet_services_betcalculator' /* sv key */ ,
//     [
//       'title' => 'Bet calculator', 'description' => 'Calculate points for a bet', 'subtype' => 'calculator',
//       'available' => true, 'priority' => 50, 'quality' => 50,
//       'os' => '', 'exec' => '',
//       'classFile' => \Sys25\RnBase\Utility\Extensions::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_betcalculator.php',
//       'className' => 'tx_t3sportsbet_services_betcalculator',
//     ]
// );

if (TYPO3_MODE === 'BE' && !\Sys25\RnBase\Utility\TYPO3::isTYPO121OrHigher()) {
    \Sys25\RnBase\Utility\Extensions::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3sportsbet/Configuration/page.tsconfig">');
}
