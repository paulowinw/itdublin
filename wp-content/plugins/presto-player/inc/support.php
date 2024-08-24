<?php

if ( ! function_exists( 'presto_player' ) ) :
	function presto_player( $id ) {
		return do_shortcode( '[presto_player id=' . $id . ']' );
	}
endif;
