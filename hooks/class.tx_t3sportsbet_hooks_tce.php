<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2010 Rene Nitzsche (rene@system25.de)
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
 * Hook-Klasse.
 */
class tx_t3sportsbet_hooks_tce
{
    /**
     * Dieser Hook wird vor der Darstellung eines TCE-Formulars aufgerufen.
     * Werte aus der Datenbank können vor deren Darstellung manipuliert werden.
     */
    public function getMainFields_preProcess($table, &$row, $tceform)
    {
        if ('tx_t3sportsbet_betsets' == $table) {
            $betgame = intval(Tx_Rnbase_Utility_T3General::_GP('betgame'));
            if ($betgame) {
                $row['betgame'] = $betgame;
            }
            $round = intval(Tx_Rnbase_Utility_T3General::_GP('round'));
            if ($round) {
                $row['round'] = $round;
            }
        }
        if ('tx_t3sportsbet_teamquestions' == $table) {
            tx_rnbase::load('tx_rnbase_util_Dates');
            $row['openuntil'] = $row['openuntil'] ? tx_rnbase_util_Dates::datetime_mysql2tstamp($row['openuntil']) : time();
        }
    }

    /**
     * Nachbearbeitungen, unmittelbar BEVOR die Daten gespeichert werden.
     * Das POST bezieht sich
     * auf die Arbeit der TCE und nicht auf die Speicherung in der DB.
     *
     * @param string $status
     *            new oder update
     * @param string $table
     *            Name der Tabelle
     * @param int $id
     *            UID des Datensatzes
     * @param array $fieldArray
     *            Felder des Datensatzes, die sich ändern
     * @param tce_main $tcemain
     */
    public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$tce)
    {
        if ('tx_t3sportsbet_teamquestions' == $table) {
            if (array_key_exists('openuntil', $fieldArray)) {
                tx_rnbase::load('tx_rnbase_util_Dates');
                $fieldArray['openuntil'] = tx_rnbase_util_Dates::datetime_tstamp2mysql($fieldArray['openuntil']);
            }
        }
    }
}
