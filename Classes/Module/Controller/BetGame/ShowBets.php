<?php
namespace Sys25\T3sportsbet\Module\Controller\BetGame;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2018 Rene Nitzsche (rene@system25.de)
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
 * Die Klasse verwaltet die Erstellung Teams für Wettbewerbe
 */
class ShowBets
{
    protected $doc;

    /**
     * Verwaltet die Erstellung von Spielplänen von Ligen
     *
     * @param tx_rnbase_mod_IModule $module
     * @param tx_cfcleague_models_Competition $competition
     */
    public function handleRequest($module, $currentRound)
    {
        // Zuerst mal müssen wir die passende Liga auswählen lassen:
        // Entweder global über die Datenbank oder die Ligen der aktuellen Seite
        $pid = $module->getPid();
        $this->doc = $module->getDoc();

        $this->formTool = $module->getFormTool();

//         $content .= $this->handleShowBets($currentRound);
//         $content .= $this->handleResetBets($currentRound);
//         $content .= $this->handleSaveBetSet($currentRound);
//         $content .= $this->handleAnalyzeBets($currentGame);

        $content = 'BIN HIER';

        return $content;
    }

    /**
     * Returns the formtool
     *
     * @return \tx_rnbase_util_FormTool
     */
    protected function getFormTool()
    {
        return $this->formTool;
    }

}
