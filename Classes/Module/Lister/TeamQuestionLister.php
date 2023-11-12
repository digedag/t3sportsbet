<?php

namespace Sys25\T3sportsbet\Module\Lister;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2023 René Nitzsche (rene@system25.de)
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
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Backend\Utility\BEPager;
use Sys25\RnBase\Backend\Utility\Tables;
use Sys25\RnBase\Utility\T3General;
use Sys25\T3sportsbet\Module\Decorator\TeamQuestionDecorator;
use Sys25\T3sportsbet\Utility\ServiceRegistry;
use tx_rnbase;

/**
 * List team bets.
 */
class TeamQuestionLister
{
    private $mod;

    private $data;

    private $SEARCH_SETTINGS;
    private $options;
    private $formTool;
    private $resultSize;

    private $betsetUid;

    /**
     * Constructor.
     *
     * @param tx_rnbase_mod_IModule $mod
     * @param array $options
     */
    public function __construct($mod, $options = [])
    {
        $this->init($mod, $options);
    }

    /**
     * Init object.
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
        $this->data = T3General::_GP('searchdata');

        if (!isset($options['nopersist'])) {
            $this->SEARCH_SETTINGS = BackendUtility::getModuleData([
                'searchterm' => '',
            ], $this->data, $this->mod->getName());
        } else {
            $this->SEARCH_SETTINGS = $this->data;
        }
    }

    /**
     * TODO: Returns the complete search form.
     */
    public function getSearchForm()
    {
        return '';
    }

    /**
     * @return tx_rnbase_mod_IModule
     */
    private function getModule()
    {
        return $this->mod;
    }

    public function setBetsetUid($uid)
    {
        $this->betsetUid = $uid;
    }

    public function getResultList()
    {
        $pager = tx_rnbase::makeInstance(BEPager::class, 'teamQuestionPager', $this->getMod()->getName(), $this->getMod()->getPid());
        // Get company service
        $srv = ServiceRegistry::getTeamBetService();

        // Set options
        $options = ['count' => 1];

        $fields = [];
        // Set filter
        if ($this->betsetUid) {
            $fields['TEAMQUESTION.BETSET'] = [
                OP_EQ_INT => $this->betsetUid,
            ];
        }

        // Set more options
        $options['orderby']['TEAMQUESTION.SORTING'] = 'ASC';
        // $options['enablefieldsfe'] = 1;

        // Get counted data
        $cnt = $srv->searchTeamQuestion($fields, $options);
        unset($options['count']);
        $pager->setListSize($cnt);
        $pager->setOptions($options);

        // Get data
        $items = $srv->searchTeamQuestion($fields, $options);
        $ret = [];
        $content = '';
        $this->showTeamQuestions($content, $items);
        $ret['table'] = $content;
        $ret['totalsize'] = $cnt;
        $pagerData = $pager->render();
        $ret['pager'] = '<div class="pager row"><span class="col-sm-2">'.$pagerData['limits'].'</span><span class="col-sm-2">'.$pagerData['pages'].'</span></div>';

        return $ret;
    }

    /**
     * Start creation of result list.
     *
     * @param string $content
     * @param array $items
     */
    private function showTeamQuestions(&$content, $items)
    {
        if (empty($items)) {
            $out = '<strong>###LABEL_MSG_NO_ITEMS_FOUND###</strong>';
            $content .= $this->mod->getDoc()->section('', $out, 0, 1, IModFunc::ICON_INFO);

            return;
        }

        $decor = tx_rnbase::makeInstance(TeamQuestionDecorator::class, $this->getModule());
        $columns = [
            'uid' => [
                'title' => 'label_uid',
                'decorator' => $decor,
            ],
            'question' => [
                'title' => 'label_question',
            ],
            'points' => [
                'title' => 'label_points',
            ],
            'openuntil' => [
                'title' => 'label_openuntil',
            ],
        ];

        /** @var Tables $tables */
        $tables = tx_rnbase::makeInstance(Tables::class);
        $arr = $tables->prepareTable($items, $columns, $this->formTool, $this->options);
        $out = $tables->buildTable($arr[0]);

        $content .= $out;
    }

    /**
     * Method to get the number of data records
     * Works only if the result list has been retrieved.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->resultSize;
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule.
     *
     * @return tx_rnbase_mod_IModule
     */
    private function getMod()
    {
        return $this->mod;
    }
}
