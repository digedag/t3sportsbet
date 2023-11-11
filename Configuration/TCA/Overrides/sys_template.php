<?php


if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

call_user_func(function () {
    $extKey = 't3sportsbet';

    // list static templates in templates selection
    \Sys25\RnBase\Utility\Extensions::addStaticFile($extKey, 'Configuration/TypoScript/Plugin/', 'T3sports Bet-System');
});
