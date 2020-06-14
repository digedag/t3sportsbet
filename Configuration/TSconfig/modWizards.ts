mod.wizards {
	newContentElement.wizardItems.plugins {
		elements {
			t3sports_bet {
				iconIdentifier = t3sports_plugin
				title = LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db:plugin.t3sportsbet.label
				description = LLL:EXT:t3sportsbet/Resources/Private/Language/locallang_db:plugin.t3sportsbet.description
				tt_content_defValues {
					CType = list
					list_type = tx_t3sportsbet_main
				}
			}
		}
	}
}
