<?php

namespace PrestoPlayer\Integrations\BeaverBuilder;

use PrestoPlayer\Models\ReusableVideo;
use PrestoPlayer\Integrations\BeaverBuilder\ReusableVideoModule\Module;


class BeaverBuilder {

	public function register() {
		add_action( 'init', array( $this, 'module' ) );
	}

	/**
	 * Register module
	 *
	 * @return void
	 */
	public function module() {
		if ( ! class_exists( '\FLBuilder' ) ) {
			return;
		}

		define( 'PRESTO_PLAYER_BB_DIR', plugin_dir_path( __FILE__ ) );
		define( 'PRESTO_PLAYER_BB_URL', plugins_url( '/', __FILE__ ) );

		\FLBuilder::register_module( Module::class, Module::getSettings() );
	}
}
