<?php

namespace PrestoPlayer\Database\Upgrades;

use PrestoPlayer\Models\Setting;

class PerformanceUpgrade {

	protected $name = 'presto_player_pro_update_performance';

	public function migrate() {
		if ( get_option( $this->name, false ) ) {
			return;
		}

		// plugin has not yet been installed, default to off
		if ( ! get_option( 'presto_player_visits_database_version', 0 ) ) {
			update_option( $this->name, true, 'no' );
			return;
		}

		// turn on setting if not yet set
		Setting::set( 'performance', 'module_enabled', true );

		update_option( $this->name, true, 'no' );
	}
}
