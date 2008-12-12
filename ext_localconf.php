<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Hook for match search
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['cfc_league_fe']['search_Match_getTableMapping_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_searchMatch.php:&tx_t3sportsbet_hooks_searchMatch->getTableMapping';
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['cfc_league_fe']['search_Match_getJoins_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_searchMatch.php:&tx_t3sportsbet_hooks_searchMatch->getJoins';

// Hook for feuser search
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['t3users']['search_feuser_getTableMapping_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_searchFeuser.php:&tx_t3sportsbet_hooks_searchFeuser->getTableMapping';
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['t3users']['search_feuser_getJoins_hook'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_searchFeuser.php:&tx_t3sportsbet_hooks_searchFeuser->getJoins';

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_t3sportsbet_hooks_tce.php:tx_t3sportsbet_hooks_tce';

t3lib_extMgm::addService($_EXTKEY,  't3sportsbet' /* sv type */,  'tx_t3sportsbet_services_bet' /* sv key */,
  array(
    'title' => 'Bet game', 'description' => 'Working with bet games', 'subtype' => 'bet',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_bet.php',
    'className' => 'tx_t3sportsbet_services_bet',
  )
);

t3lib_extMgm::addService($_EXTKEY,  't3sportsbet' /* sv type */,  'tx_t3sportsbet_services_betcalculator' /* sv key */,
  array(
    'title' => 'Bet calculator', 'description' => 'Calculate points for a bet', 'subtype' => 'calculator',
    'available' => TRUE, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => t3lib_extMgm::extPath($_EXTKEY).'services/class.tx_t3sportsbet_services_betcalculator.php',
    'className' => 'tx_t3sportsbet_services_betcalculator',
  )
);

// always load service registry
require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');
tx_div::load('tx_t3sportsbet_util_serviceRegistry');


?>
