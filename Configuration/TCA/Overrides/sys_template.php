<?php

defined('TYPO3_MODE') or die();

call_user_func(function () {
    $extKey = 't3sportsbet';

    // list static templates in templates selection
    tx_rnbase_util_Extensions::addStaticFile($extKey, 'Configuration/TypoScript/Plugin/', 'T3sports Bet-System');
});
