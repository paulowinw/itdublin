<?php

namespace PrestoPlayer\Integrations;

class Kadence {

	public function register() {
		add_filter( 'presto_player_default_color', array( $this, 'defaultColor' ) );
	}

	public function defaultColor( $color ) {
		if ( function_exists( '\Kadence\kadence' ) ) {
			return \Kadence\kadence()->palette_option( 'palette1' );
		}
		return $color;
	}
}
