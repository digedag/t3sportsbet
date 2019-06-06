<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


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

    ////////////////////////////////
    // Submodul anmelden
    ////////////////////////////////
	$modName = 'web_CfcLeagueM1';

//	require_once(tx_rnbase_util_Extensions::extPath($_EXTKEY) .'util/class.tx_t3sportsbet_util_ItemFunctions.php');
	tx_rnbase_util_Extensions::insertModuleFunction($modName, \Sys25\T3sportsbet\Controller\BetGame::class,
	    tx_rnbase_util_Extensions::extPath($_EXTKEY) . 'Classes/Controller/BetGame.php',
		'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_module.name'
	);
}
