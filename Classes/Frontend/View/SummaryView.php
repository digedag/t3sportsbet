<?php

namespace Sys25\T3sportsbet\Frontend\View;

use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\ContextInterface;
use Sys25\RnBase\Frontend\View\Marker\BaseView;

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
 * Viewklasse für die Darstellung von Nutzerinformationen aus der DB.
 */
class SummaryView extends BaseView
{
    /**
     * @param string $template
     * @param RequestInterface $request
     * @param tx_rnbase_util_FormatUtil $formatter
     *
     * @return string
     */
    protected function createOutput($template, RequestInterface $request, $formatter)
    {
        // Wir holen die Daten von der Action ab
        $data = &$request->getViewContext()->offsetGet('data');
        $out = $data;

        return $out;
    }

    /**
     * Subpart der im HTML-Template geladen werden soll.
     * Dieser wird der Methode
     * createOutput automatisch als $template übergeben.
     *
     * @return string
     */
    protected function getMainSubpart(ContextInterface $viewData)
    {
        return '###SUMMARY###';
    }
}
