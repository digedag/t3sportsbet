<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Hook for match search
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['cfc_league_fe']['search_Match_getTableMapping_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_Search.php:&tx_t3sportsbet_hooks_Search->getTableMappingMatch';
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['cfc_league_fe']['search_Match_getJoins_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_Search.php:&tx_t3sportsbet_hooks_Search->getJoinsMatch';

// Hook for team search
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Team_getTableMapping_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_Search.php:&tx_t3sportsbet_hooks_Search->getTableMappingTeam';
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['cfc_league']['search_Team_getJoins_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_Search.php:&tx_t3sportsbet_hooks_Search->getJoinsTeam';

// Hook for feuser search
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['t3users']['search_feuser_getTableMapping_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_searchFeuser.php:&tx_t3sportsbet_hooks_searchFeuser->getTableMapping';
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['t3users']['search_feuser_getJoins_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_searchFeuser.php:&tx_t3sportsbet_hooks_searchFeuser->getJoins';

// Hook for team marker
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['cfc_league_fe']['teamMarker_initRecord'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_Marker.php:&tx_t3sportsbet_hooks_Marker->initTeam';


$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_tce.php:tx_t3sportsbet_hooks_tce';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_tce.php:tx_t3sportsbet_hooks_tce';

tx_rnbase_util_Extensions::addService($_EXTKEY,  't3sportsbet' /* sv type */,  'tx_t3sportsbet_services_bet' /* sv key */,
  array(
    'title' => 'Bet game', 'description' => 'Working with bet games', 'subtype' => 'bet',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_bet.php',
    'className' => 'tx_t3sportsbet_services_bet',
  )
);

tx_rnbase_util_Extensions::addService($_EXTKEY,  't3sportsbet' /* sv type */,  'tx_t3sportsbet_services_teambet' /* sv key */,
  array(
    'title' => 'Teambets', 'description' => 'Working with team bets', 'subtype' => 'teambet',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_teambet.php',
    'className' => 'tx_t3sportsbet_services_teambet',
  )
);

tx_rnbase_util_Extensions::addService($_EXTKEY,  't3sportsbet' /* sv type */,  'tx_t3sportsbet_services_betcalculator' /* sv key */,
  array(
    'title' => 'Bet calculator', 'description' => 'Calculate points for a bet', 'subtype' => 'calculator',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_betcalculator.php',
    'className' => 'tx_t3sportsbet_services_betcalculator',
  )
);

// always load service registry
tx_rnbase::load('tx_t3sportsbet_util_serviceRegistry');
