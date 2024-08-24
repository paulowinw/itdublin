<?php

namespace PrestoPlayer\Integrations\Tutor;

class Tutor {

	public function register() {
		add_filter( 'tutor_course/single/video', array( $this, 'renderVideo' ) );
		add_filter( 'tutor_lesson/single/video', array( $this, 'renderVideo' ) );
	}

	public function renderVideo( $output ) {
		if ( ! strpos( $output, '[presto_player' ) ) {
			return $output;
		}

		// read all image tags into an array
		preg_match_all( '/<source[^>]+>/i', $output, $source );

		if ( empty( $source[0] ) ) {
			return $output;
		}

		preg_match( '/src="([^"]+)/i', $source[0][0], $src );

		if ( empty( $src[1] ) ) {
			return $output;
		}

		// remove accidental url encoding and protocol.
		$source = str_replace( 'http://', '', $src[1] );
		$source = str_replace( 'https://', '', $source );
		$source = str_replace( '%20', ' ', $source );

		return \do_shortcode( $source );
	}
}
