<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2010 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Extend marker classes
 *
 * @author Rene Nitzsche
 */
class tx_t3sportsbet_hooks_Marker {

	/**
	 * Extend teamMarker for currently selected teambet team
	 * @param array $params
	 * @param tx_cfcleaguefe_util_TeamMarker $parent
	 */
	public function initTeam($params, $parent) {
		$options = $parent->getOptions();
		if(! (is_array($options) && array_key_exists('teambet', $options)) ) return;

		$params['item']->setProperty('currentbet', $params['item']->getUid() == $options['teambet'] ? 1 : '');
	}
}
