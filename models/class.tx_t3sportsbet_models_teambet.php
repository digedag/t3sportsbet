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

tx_rnbase::load('tx_rnbase_model_base');

/**
 * Model for a bet.
 */
class tx_t3sportsbet_models_teambet extends tx_rnbase_model_base
{

    function getTableName()
    {
        return 'tx_t3sportsbet_teambets';
    }

    /**
     * Returns the team question
     *
     * @return tx_t3sportsbet_models_teamquestion
     */
    public function getTeamQuestion()
    {
        return tx_t3sportsbet_util_serviceRegistry::getTeamBetService()->loadTeamQuestion($this->getTeamQuestionUid());
    }

    public function getTeamQuestionUid()
    {
        return (int) $this->getProperty('question');
    }

    /**
     * Possible points
     * 
     * @return int
     */
    public function getPossiblePoints()
    {
        return (int)$this->getProperty('possiblepoints');
    }

    /**
     * Team
     * 
     * @return int
     */
    public function getTeamUid()
    {
        return (int) $this->getProperty('team');
    }

    /**
     * Team
     * 
     * @return tx_cfcleague_models_Team
     */
    public function getTeam()
    {
        return tx_rnbase::makeInstance('tx_cfcleague_models_Team', $this->getProperty('team'));
    }
}
