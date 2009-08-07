<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$TCA['tx_t3sportsbet_betgames'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betgames',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_table.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, starttime, name',
	)
);

$TCA['tx_t3sportsbet_betsets'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_betsets',
		'label' => 'uid',
// If label_alt is used, the flexform fails for some reasons... huh??
//		'label_alt' => 'uid',
		'label_alt' => 'round, round_name',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => Array (
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_table.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => '',
	)
);

$TCA['tx_t3sportsbet_bets'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_bets',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'tstamp',
		'delete' => 'deleted',
		'enablecolumns' => Array (
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_table.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => '',
	)
);


////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_t3sportsbet_main']='layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_t3sportsbet_main']='pi_flexform';

$GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ',scope.betgame';

t3lib_extMgm::addPiFlexFormValue('tx_t3sportsbet_main','FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');
t3lib_extMgm::addPlugin(Array('LLL:EXT:'.$_EXTKEY.'/locallang_db.php:plugin.t3sportsbet.label','tx_t3sportsbet_main'));

t3lib_extMgm::addStaticFile($_EXTKEY,'static/ts/', 'T3sports Bet-System');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/css/', 'T3sports Bet-System (CSS)');

# Add plugin wizard
if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_t3sportsbet_util_Wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'util/class.tx_t3sportsbet_util_Wizicon.php';

////////////////////////////////
// Submodul anmelden
////////////////////////////////
if (TYPO3_MODE=="BE")	{
	require_once(t3lib_extMgm::extPath($_EXTKEY) .'util/class.tx_t3sportsbet_util_ItemFunctions.php');
	t3lib_extMgm::insertModuleFunction(
		'web_txcfcleagueM1',
		'tx_t3sportsbet_mod1_index',
		t3lib_extMgm::extPath($_EXTKEY).'mod1/class.tx_t3sportsbet_mod1_index.php',
		'LLL:EXT:t3sportsbet/locallang_db.xml:tx_t3sportsbet_module.name'
	);
}


?>