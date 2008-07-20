<?php

########################################################################
# Extension Manager/Repository config file for ext: "rn_userregister"
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'T3sports bet system',
	'description' => 'Bet-system for T3sports. FE-Users can bet on matches in T3sports.',
	'category' => 'plugin',
	'author' => 'Rene Nitzsche',
	'author_email' => 'rene@system25.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.2.0',
	'constraints' => array(
		'depends' => array(
			'php' => '5.0.0-0.0.0',
			'div' => '0.0.11-0.0.0',
			'lib' => '0.1.0-0.0.0',
			'rn_base' => '0.2.5-0.0.0',
			'cfc_league' => '0.4.5-0.0.0',
			'cfc_league_fe' => '0.4.0-0.0.0',
			't3users' => '0.1.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:8:{s:9:"ChangeLog";s:4:"690e";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"fc02";s:19:"doc/wizard_form.dat";s:4:"d0ae";s:20:"doc/wizard_form.html";s:4:"1048";s:23:"static/ts/constants.txt";s:4:"96ef";s:19:"static/ts/setup.txt";s:4:"f531";}',
);

?>