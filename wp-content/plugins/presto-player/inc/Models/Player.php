<?php

namespace PrestoPlayer\Models;

class Player {

	public static $branding_key = 'presto_player_branding';

	public static function postHasPlayer( $id ) {
		// global is the most reliable between page builders
		global $load_presto_js;
		if ( $load_presto_js ) {
			return true;
		}

		// change to see if we have one of our blocks
		$types = Block::getBlockTypes();
		foreach ( $types as $type ) {
			if ( has_block( $type, $id ) ) {
				return true;
			}
		}

		// check for data-presto-config (player rendered)
		$wp_post = get_post( $id );
		if ( $wp_post instanceof \WP_Post ) {
			$post = $wp_post->post_content;
		}
		$has_player = false !== strpos( $post, '<presto-player' );
		if ( $has_player ) {
			return true;
		}

		// check that we have a shortcode
		if ( has_shortcode( $post, 'presto_player' ) ) {
			return true;
		}

		// enable on Elementor
		if ( ! empty( $_GET['action'] ) && 'elementor' === $_GET['action'] ) {
			return true;
		}
		if ( isset( $_GET['elementor-preview'] ) ) {
			return true;
		}

		// load for beaver builder
		if ( isset( $_GET['fl_builder'] ) ) {
			return true;
		}

		// do we have the player
		return $has_player;
	}

	/**
	 * Get get branding settings
	 *
	 * @return array
	 */
	public static function getBranding() {
		$defaults = array(
			'logo'       => '',
			'logo_width' => 150,
			'color'      => '#00b3ff',
		);
		return self::get_option( self::$branding_key, $defaults );
	}

	/**
	 * Revert to option default in case it's empty
	 *
	 * @param string $key
	 * @param array  $defaults
	 * @return array
	 */
	public static function get_option( $key, $defaults ) {
		$config = get_option( $key, $defaults );
		return ! empty( $config ) ? $config : $defaults;
	}
}
