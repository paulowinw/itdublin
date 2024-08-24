<?php

namespace PrestoPlayer\Database\Upgrades;

use PrestoPlayer\Plugin;

class Upgrades {

	public function migrate() {
		if ( Plugin::isPro() ) {
			( new VisitsUpgrade() )->run();
			( new TransientsUpgrade() )->run();
		}
	}
}
