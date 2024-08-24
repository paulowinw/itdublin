<?php

namespace PrestoPlayer\Support;

class Integration {

	/**
	 * Register integration filter
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'presto_player_load_video', array( $this, 'allow' ), 10, 4 );
	}

	/**
	 * Whether to allow the player to load
	 *
	 * @param bool   $load
	 * @param array  $attributes
	 * @param string $content
	 * @param string $name
	 *
	 * @return bool
	 */
	public function allow( $load, $attributes, $content, $name ) {
		return $load;
	}
}
