<?php

namespace Sys25\T3sportsbet\Module\Lister;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2023 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Sys25\RnBase\Backend\Module\IModFunc;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Backend\Utility\BEPager;
use Sys25\RnBase\Backend\Utility\Tables;
use Sys25\RnBase\Utility\T3General;
use Sys25\T3sportsbet\Module\Decorator\BetDecorator;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use tx_rnbase;
use tx_rnbase_mod_IModule;

/**
 * Search match bets from database.
 */
class MatchBetLister
{
    /** @var tx_rnbase_mod_IModule */
    private $mod;

    private $data;

    private $SEARCH_SETTINGS;

    private $betsetUid;

    private $matchUids;
    private $options;
    private $formTool;
    private $resultSize;

    public function __construct($mod, $options = [])
    {
        $this->init($mod, $options);
    }

    private function init($mod, $options)
    {
        $this->options = $options;
        $this->mod = $mod;
        $this->formTool = $mod->getFormTool();
        $this->resultSize = 0;
        $this->data = T3General::_GP('searchdata');

        if (!isset($options['nopersist'])) {
            $this->SEARCH_SETTINGS = BackendUtility::getModuleData([
                'searchterm' => '',
            ], $this->data, $this->getModule()->getName());
        } else {
            $this->SEARCH_SETTINGS = $this->data;
        }
    }

    public function setMatchUids($uids)
    {
        $this->matchUids = $uids;
    }

    public function setBetSetUid($uid)
    {
        $this->betsetUid = $uid;
    }

    /**
     * Liefert das Suchformular.
     * Hier die beiden Selectboxen anzeigen.
     *
     * @param string $label
     *            Alternatives Label
     *
     * @return string
     */
    public function getSearchForm($label = '')
    {
        $out = '';

        return $out;
    }

    public function getResultList()
    {
        /** @var BEPager $pager */
        $pager = tx_rnbase::makeInstance(BEPager::class, 'matchBetPager', $this->getModule()->getName(), $this->getModule()->getPid());
        $srv = ServiceRegistry::getBetService();

        // Set options
        $options = [
            'count' => 1,
        ];
        $fields = [];
        // Set filter
        if ($this->betsetUid) {
            $fields['BET.BETSET'] = [
                OP_EQ_INT => $this->betsetUid,
            ];
        }
        if ($this->matchUids) {
            $fields['BET.T3MATCH'] = [
                OP_IN_INT => $this->matchUids,
            ];
        }

        $options['orderby']['BET.TSTAMP'] = 'desc';
        $cnt = $srv->searchBet($fields, $options);
        unset($options['count']);
        $pager->setListSize($cnt);
        $pager->setOptions($options);

        $items = $srv->searchBet($fields, $options);
        $ret = [];

        $ret['table'] = $this->showBets($items);
        $ret['totalsize'] = $cnt;
        $pagerData = $pager->render();
        $ret['pager'] = '<div class="pager row"><span class="col-sm-2">'.$pagerData['limits'].'</span><span class="col-sm-2">'.$pagerData['pages'].'</span></div>';

        return $ret;
    }

    /**
     * Liefert die Anzahl der gefunden Datensätze.
     * Funktioniert natürlich erst, nachdem die Ergebnisliste abgerufen wurde.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->resultSize;
    }

    private function showBets($bets)
    {
        if (empty($bets)) {
            $out = '<strong>'.$GLOBALS['LANG']->getLL('msg_no_bets_found').'</strong>';

            return $this->getModule()->getDoc()->section('', $out, 0, 1, IModFunc::ICON_INFO);
        }
        $decor = tx_rnbase::makeInstance(BetDecorator::class);
        $decor->setFormTool($this->formTool);
        $columns = [
            'uid' => [
                'decorator' => $decor,
                'title' => 'label_uid',
            ],
            'tstamp' => [
                'decorator' => $decor,
                'title' => 'label_tstamp',
            ],
            't3match' => [
                'decorator' => $decor,
                'title' => 'label_match',
            ],
            't3matchresult' => [
                'decorator' => $decor,
                'title' => 'label_result',
            ],
            'bet' => [
                'decorator' => $decor,
                'title' => 'label_bet',
            ],
            'points' => [
                'decorator' => $decor,
                'title' => 'label_points',
            ],
            'fe_user' => [
                'decorator' => $decor,
                'title' => 'label_feuser',
            ],
        ];

        /** @var Tables $tables */
        $tables = tx_rnbase::makeInstance(Tables::class);
        $rows = $tables->prepareTable($bets, $columns, $this->formTool, $this->options);
        $out = $tables->buildTable($rows[0]);

        return $out;
    }

    /**
     * @return IModule
     */
    private function getModule()
    {
        return $this->mod;
    }
}
