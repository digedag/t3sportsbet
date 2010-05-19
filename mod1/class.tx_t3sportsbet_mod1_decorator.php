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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Die Klasse zeigt Objekte im Backend an.
 */
class tx_t3sportsbet_mod1_decorator {

	static function prepareRecords($records, $columns, $formTool, $options) {
		// Ist kein Wettbewerb vorhanden, dann wird nur das Endergebnis angezeigt
		$arr = Array( 0 => Array( self::getHeadline($columns, $options) ));
		foreach($records As $record){
			$dataArr = is_object($record) ? $record->record : $record;
			
			$row = array();
			if(isset($options['checkbox'])) {
				// Check if record is checkable
				if(!is_array($options['dontcheck']) || !array_key_exists($dataArr['uid'], $options['dontcheck']))
					$row[] = $formTool->createCheckbox('checkMatch[]', $dataArr['uid']);
				else
					$row[] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/zoom2.gif','width="11" height="12"').' title="Info: '. $options['dontcheck'][$dataArr['uid']] .'" border="0" alt="" />';
			}
			reset($columns);
			foreach($columns As $column => $data) {
				// Hier erfolgt die Ausgabe der Daten für die Tabelle. Wenn eine method angegeben
				// wurde, dann muss das Spiel als Objekt vorliegen. Es wird dann die entsprechende
				// Methode aufgerufen. Es kann auch ein Decorator-Objekt gesetzt werden. Dann wird
				// von diesem die Methode format aufgerufen und der Wert, sowie der Name der aktuellen
				// Spalte übergeben. Ist nichts gesetzt wird einfach der aktuelle Wert verwendet.
				if(isset($data['method'])) {
					$row[] = call_user_func(array($record, $data['method']));
				}
				elseif(isset($data['decorator'])) {
					$decor = $data['decorator'];
					$row[] = $decor->format(isset($dataArr[$column]) ? $dataArr[$column] : $record, $column);
				}
				else {
					$row[] = $dataArr[$column];
				}
			}
			if(isset($options['linker']))
				$row[] = self::addLinker($options, $record, $formTool);
			$arr[0][] = $row;
		}

		return $arr;
	}
	/**
	 * Liefert die passenden Überschrift für die Tabelle
	 *
	 * @param array $columns
	 * @param array $options
	 * @return array
	 */
	static function getHeadline($columns, $options) {
		global $LANG;
		$arr = array();
		if(isset($options['checkbox'])) {
			$arr[] = '&nbsp;'; // Spalte für Checkbox
		}
		foreach($columns As $column => $data) {
			if(intval($data['nocolumn'])) continue;
			$arr[] = intval($data['notitle']) ? '' :
					$LANG->getLL((isset($data['title']) ? $data['title'] : $column));
		}
		if(isset($options['linker']))
			$arr[] = $LANG->getLL('label_action');
    return $arr;
  }
	static function addLinker($options, $obj, $formTool) {
		$out = '';
		if(isset($options['linker'])) {
			$linkerArr = $options['linker'];
			if(is_array($linkerArr) && count($linkerArr)) {
				$currentPid = intval($options['pid']);
				foreach($linkerArr As $linker) {
					$out .= $linker->makeLink($obj, $formTool, $currentPid, $options);
					$out .= $options['linkerimplode'] ? $options['linkerimplode'] : '<br />';
				}
			}
		}
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_decorator.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3sportsbet/mod1/class.tx_t3sportsbet_mod1_decorator.php']);
}


?>
