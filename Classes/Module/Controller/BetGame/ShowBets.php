<?php

namespace Sys25\T3sportsbet\Module\Controller\BetGame;

use Sys25\RnBase\Backend\Module\IModule;
use Sys25\T3sportsbet\Model\BetSet;
use Sys25\T3sportsbet\Module\Lister\MatchBetLister;
use tx_rnbase;

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

/**
 * Die Klasse verwaltet die Erstellung Teams für Wettbewerbe.
 */
class ShowBets
{
    protected $doc;

    /**
     * @var IModule
     */
    protected $module;

    /**
     * @var BetSet
     */
    protected $currentRound;
    private $formTool;

    /**
     * Verwaltet die Erstellung von Spielplänen von Ligen.
     *
     * @param IModule $module
     * @param BetSet $currentRound
     */
    public function __construct($module, $currentRound)
    {
        $this->module = $module;
        $this->doc = $module->getDoc();

        $this->formTool = $module->getFormTool();
        $this->currentRound = $currentRound;
    }

    /**
     * @return string
     */
    public function handleRequest()
    {
        return '';
    }

    /**
     * @return string
     */
    public function show()
    {
        // Alle Tips für dieses Betset suchen
        $lister = tx_rnbase::makeInstance(MatchBetLister::class, $this->module, []);
        $lister->setBetSetUid($this->currentRound->getUid());
        $list = $lister->getResultList();
        $out = $list['pager']."\n".$list['table'];

        return '<h2>###LABEL_BETLIST###</h2>'.$out;
        //        return $this->doc->section('###LABEL_BETLIST###'.':',$out,0,1,\tx_rnbase_mod_IModFunc::ICON_INFO);
    }
}
