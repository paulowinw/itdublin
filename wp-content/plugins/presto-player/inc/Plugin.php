<?php

namespace PrestoPlayer;

class Plugin {

	public static function isPro() {
		return defined( 'PRESTO_PLAYER_PRO_ENABLED' );
	}

	public static function requiredProVersion() {
		return '0.0.3';
	}

	/**
	 * Get the version from plugin data
	 *
	 * @return string
	 */
	public static function version() {
		// Load version from plugin data.
		if ( ! \function_exists( 'get_plugin_data' ) ) {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return \get_plugin_data( PRESTO_PLAYER_PLUGIN_FILE, false, false )['Version'];
	}

	public static function proVersion() {
		if ( ! self::isPro() ) {
			return false;
		}
		if ( class_exists( '\PrestoPlayer\Pro\Plugin' ) ) {
			return \PrestoPlayer\Pro\Plugin::version();
		}
		return false;
	}
}
