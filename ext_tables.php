<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

// Add plugin wizard
if (\Sys25\RnBase\Utility\Environment::isBackend()) {
    if (!\Sys25\RnBase\Utility\TYPO3::isTYPO121OrHigher()) {
        // //////////////////////////////
        // Submodul anmelden
        // //////////////////////////////
        $modName = 'web_CfcLeagueM1';

        \Sys25\RnBase\Utility\Extensions::insertModuleFunction(
            $modName,
            \Sys25\T3sportsbet\Module\Controller\BetGame::class,
            '',
            'LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db.xlf:tx_t3sportsbet_module.name'
        );
    }

    $iconRegistry = tx_rnbase::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $icons = [
        'ext-t3sportsbet-betgame-default' => 'icon_table.gif',
        'ext-t3sportsbet-bet-default' => 'ext_icon.svg',
        'ext-t3sportsbet-betset-default' => 'icon_table.gif',
        'ext-t3sportsbet-betgameresult-default' => 'icon_table.gif',
        'ext-t3sportsbet-teambet-default' => 'icon_table.gif',
        'ext-t3sportsbet-teamquestion-default' => 'icon_table.gif',
    ];
    foreach ($icons as $identifier => $path) {
        $iconRegistry->registerIcon($identifier, \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class, [
            'source' => 'EXT:t3sportsbet/Resources/Public/Icons/'.$path,
        ]);
    }
}
