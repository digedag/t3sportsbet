<?php

defined('TYPO3_MODE') or die();

call_user_func(function () {
    $extKey = 't3sportsbet';

    ////////////////////////////////
    // Plugin anmelden
    ////////////////////////////////

    // Einige Felder ausblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['tx_t3sportsbet_main'] = 'layout,select_key,pages,recursive';

    // Das tt_content-Feld pi_flexform einblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tx_t3sportsbet_main'] = 'pi_flexform';

    $GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ',scope.betgame';

    tx_rnbase_util_Extensions::addPiFlexFormValue(
        'tx_t3sportsbet_main',
        'FILE:EXT:'.$extKey.'/Configuration/Flexform/flexform_main.xml'
    );

    tx_rnbase_util_Extensions::addPlugin(
        [
            'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang_db.xml:plugin.t3sportsbet.label',
            'tx_t3sportsbet_main',
        ],
        'list_type',
        $extKey
    );
});
