<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


if(!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
    // TCA registration for 4.5
    $TCA['tx_t3sportsbet_betgames'] = require tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/locallang_db.xml:tx_t3sportsbet_betgames.php';
    $TCA['tx_t3sportsbet_betsets'] = require tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/locallang_db.xml:tx_t3sportsbet_betsets.php';
    $TCA['tx_t3sportsbet_betsetresults'] = require tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/locallang_db.xml:tx_t3sportsbet_betsetresults.php';
    $TCA['tx_t3sportsbet_bets'] = require tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/locallang_db.xml:tx_t3sportsbet_bets.php';
    $TCA['tx_t3sportsbet_teamquestions'] = require tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/locallang_db.xml:tx_t3sportsbet_teamquestions.php';
    $TCA['tx_t3sportsbet_teambets'] = require tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/locallang_db.xml:tx_t3sportsbet_teambets.php';
}


////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_t3sportsbet_main']='layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_t3sportsbet_main']='pi_flexform';

$GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ',scope.betgame';

tx_rnbase_util_Extensions::addPiFlexFormValue('tx_t3sportsbet_main','FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');
tx_rnbase_util_Extensions::addPlugin(Array('LLL:EXT:'.$_EXTKEY.'/locallang_db.php:plugin.t3sportsbet.label','tx_t3sportsbet_main'));

tx_rnbase_util_Extensions::addStaticFile($_EXTKEY,'static/ts/', 'T3sports Bet-System');
tx_rnbase_util_Extensions::addStaticFile($_EXTKEY,'static/css/', 'T3sports Bet-System (CSS)');

# Add plugin wizard
if (TYPO3_MODE=='BE')	{
	tx_rnbase::load('tx_rnbase_util_Wizicon');
	tx_rnbase_util_Wizicon::addWizicon('tx_t3sportsbet_util_Wizicon', tx_rnbase_util_Extensions::extPath($_EXTKEY).'util/class.tx_t3sportsbet_util_Wizicon.php');
}

////////////////////////////////
// Submodul anmelden
////////////////////////////////
if (TYPO3_MODE=="BE")	{
    $modName = 'web_txcfcleagueM1';
    if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
        $modName = 'web_CfcLeagueM1';
    }
    require_once(tx_rnbase_util_Extensions::extPath($_EXTKEY) .'util/class.tx_t3sportsbet_util_ItemFunctions.php');
	tx_rnbase_util_Extensions::insertModuleFunction($modName, 'tx_t3sportsbet_mod1_index',
	    tx_rnbase_util_Extensions::extPath($_EXTKEY).'mod1/class.tx_t3sportsbet_mod1_index.php',
		'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_module.name'
	);
}
