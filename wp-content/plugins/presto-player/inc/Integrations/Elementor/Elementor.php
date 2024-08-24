<?php

namespace PrestoPlayer\Integrations\Elementor;

class Elementor {

	public function register() {
		add_action( 'elementor/widgets/register', array( $this, 'widget' ) );
	}

	public function widget( $widgets_manager ) {
		$widgets_manager->register( new ReusableVideoWidget() );
	}
}
