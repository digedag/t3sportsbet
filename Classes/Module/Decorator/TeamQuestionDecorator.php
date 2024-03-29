<?php

namespace Sys25\T3sportsbet\Module\Decorator;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2023 Rene Nitzsche (rene@system25.de)
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

use Sys25\T3sportsbet\Model\TeamQuestion;

/**
 * Diese Klasse ist für die Darstellung von TeamQuestions im Backend verantwortlich.
 */
class TeamQuestionDecorator
{
    private $mod;

    public function __construct($mod)
    {
        $this->mod = $mod;
    }

    /**
     * Returns the module.
     *
     * @return tx_rnbase_mod_IModule
     */
    private function getModule()
    {
        return $this->mod;
    }

    /**
     * @param string $value
     * @param string $colName
     * @param array $record
     * @param TeamQuestion $item
     */
    public function format($value, $colName, $record, $item)
    {
        $ret = $value;
        if ('uid' == $colName) {
            $ret = $this->getModule()->getFormTool()->createEditLink('tx_t3sportsbet_teamquestions', $item->getUid(), '');
            $wrap = $item->getProperty('hidden') ? ['<strike>', '</strike>'] : ['<strong>', '</strong>'];
            $ret .= $wrap[0].$value.$wrap[1].'<br />';
        }

        return $ret;
    }
}
