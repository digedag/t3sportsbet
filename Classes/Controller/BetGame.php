<?php

namespace Sys25\T3sportsbet\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2019 Rene Nitzsche (rene@system25.de)
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
 * Die Klasse ist die Einstiegsklasse für das Modul "Tippspiel"
 */
class BetGame extends \tx_rnbase_mod_BaseModFunc
{

    private $doc, $MCONF;

    /**
     * Method getFuncId
     *
     * @return string
     */
    protected function getFuncId()
    {
        return 'funcbetgame';
    }

    /**
     *
     * @param string $template
     * @param \tx_rnbase_configurations $configurations
     * @param \tx_rnbase_util_FormatUtil $formatter
     * @param \tx_rnbase_util_FormTool $formTool
     * @return string
     */
    protected function getContent($template, &$configurations, &$formatter, $formTool)
    {
        $modContent = 'Hello MOD!';

        return $modContent;
    }
}

