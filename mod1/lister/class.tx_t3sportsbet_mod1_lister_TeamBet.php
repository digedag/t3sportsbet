<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 René Nitzsche (rene@system25.de)
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

/**
 * List team bets
 */
class tx_t3sportsbet_mod1_lister_TeamBet
{

    private $mod;

    private $data;

    private $SEARCH_SETTINGS;

    private $teamQuestionUid;

    /**
     * Constructor
     *
     * @param tx_rnbase_mod_IModule $mod
     * @param array $options
     */
    public function __construct($mod, $options = [])
    {
        $this->init($mod, $options);
    }

    /**
     * Init object
     *
     * @param tx_rnbase_mod_IModule $mod
     * @param array $options
     */
    private function init($mod, $options)
    {
        $this->options = $options;
        $this->mod = $mod;
        $this->formTool = $mod->getFormTool();
        $this->resultSize = 0;
        $this->data = \Tx_Rnbase_Utility_T3General::_GP('searchdata');

        if (! isset($options['nopersist'])) {
            $this->SEARCH_SETTINGS = \Tx_Rnbase_Backend_Utility::getModuleData([
                'searchterm' => ''
            ], $this->data, $this->mod->getName());
        }
        else {
            $this->SEARCH_SETTINGS = $this->data;
        }
    }

    /**
     * TODO: Returns the complete search form
     */
    public function getSearchForm()
    {
        return $out;
    }

    /**
     *
     * @return tx_rnbase_mod_IModule
     */
    private function getModule()
    {
        return $this->mod;
    }

    public function setTeamQuestionUid($uid)
    {
        $this->teamQuestionUid = $uid;
    }

    /**
     */
    public function getResultList()
    {
        $pager = tx_rnbase::makeInstance('tx_rnbase_util_BEPager', 'teamBetPager', $this->getMod()->getName(), $this->getMod()->getPid());
        $srv = tx_t3sportsbet_util_serviceRegistry::getTeamBetService();

        // Set options
        $options = [
            'count' => 1
        ];

        $fields = [];
        // Set filter
        if ($this->teamQuestionUid) {
            $fields['TEAMBET.QUESTION'] = [
                OP_EQ_INT => $this->teamQuestionUid
            ];
        }

        // Set more options
        $options['orderby']['TEAMBET.TSTAMP'] = 'DESC';
        // $options['enablefieldsfe'] = 1;

        // Get counted data
        $cnt = $srv->searchTeamBet($fields, $options);
        unset($options['count']);
        $pager->setListSize($cnt);
        $pager->setOptions($options);

        // Get data
        $items = $srv->searchTeamBet($fields, $options);
        $ret = [];
        $content = '';
        $this->showTeamBets($content, $items);
        $ret['table'] = $content;
        $ret['totalsize'] = $cnt;
        $pagerData = $pager->render();
        $ret['pager'] .= '<div class="pager"><span class="col-md-2">' . $pagerData['limits'] . '</span><span class="col-md-2">' . $pagerData['pages'] . '</span></div>';
        return $ret;
    }

    /**
     * Start creation of result list
     *
     * @param string $content
     * @param array $items
     */
    private function showTeamBets(&$content, $items)
    {
        $decor = tx_rnbase::makeInstance('tx_t3sportsbet_mod1_decorator_TeamBet', $this->getModule());
        $columns = [
            'uid' => [
                'title' => 'label_uid',
                'decorator' => $decor
            ],
            'tstamp' => [
                'decorator' => $decor,
                'title' => 'label_tstamp'
            ],
            'team' => [
                'title' => 'label_team',
                'decorator' => $decor
            ],
            'possiblepoints' => [
                'title' => 'label_possiblepoints'
            ],
            'points' => [
                'title' => 'label_points'
            ],
            'finished' => [
                'title' => 'label_finished',
                'decorator' => $decor
            ],
            'feuser' => [
                'decorator' => $decor,
                'title' => 'label_feuser'
            ],
        ];

        if ($items) {
            /* @var $tables Tx_Rnbase_Backend_Utility_Tables */
            $tables = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Utility_Tables');
            $arr = $tables->prepareTable($items, $columns, $this->formTool, $this->options);
            $out = $tables->buildTable($arr[0]);
        } else {
            $out = '<p><strong>###LABEL_MSG_NO_ITEMS_FOUND###</strong></p><br/>';
        }
        $content .= $out;
    }

    /**
     * Method to get the number of data records
     * Works only if the result list has been retrieved
     *
     * @return int
     */
    public function getSize()
    {
        return $this->resultSize;
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule
     *
     * @return tx_rnbase_mod_IModule
     */
    private function getMod()
    {
        return $this->mod;
    }
}
