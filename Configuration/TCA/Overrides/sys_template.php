<?php

defined('TYPO3_MODE') or exit;

call_user_func(function () {
    $extKey = 't3sportsbet';

    // list static templates in templates selection
    tx_rnbase_util_Extensions::addStaticFile($extKey, 'Configuration/TypoScript/Plugin/', 'T3sports Bet-System');
});
