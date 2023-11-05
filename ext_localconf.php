<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
  exit('Access denied.');
}

$_EXTKEY = 't3sportsbet';

// Hook for match search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Match_getTableMapping_hook'][] = 'tx_t3sportsbet_hooks_Search->getTableMappingMatch';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Match_getJoins_hook'][] = 'tx_t3sportsbet_hooks_Search->getJoinsMatch';

// Hook for team search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Team_getTableMapping_hook'][] = 'tx_t3sportsbet_hooks_Search->getTableMappingTeam';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Team_getJoins_hook'][] = 'tx_t3sportsbet_hooks_Search->getJoinsTeam';

// Hook for feuser search
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['search_FeUser_getTableMapping_hook'][] = 'tx_t3sportsbet_hooks_searchFeuser->getTableMapping';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['search_FeUser_getJoins_hook'][] = 'tx_t3sportsbet_hooks_searchFeuser->getJoins';

// Hook for team marker
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cfc_league_fe']['teamMarker_initRecord'][] = 'tx_t3sportsbet_hooks_Marker->initTeam';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'tx_t3sportsbet_hooks_tce';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'tx_t3sportsbet_hooks_tce';

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
