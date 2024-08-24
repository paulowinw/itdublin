<?php

namespace PrestoPlayer\Database\Upgrades;

use PrestoPlayer\Plugin;

/**
 * Clears pro transients every upgrade to ensure update notifications are immediate
 */
class TransientsUpgrade {

	protected $name = 'presto_player_pro_update_transient';

	public function run() {
		$this->runUpdate();
	}

	/**
	 * Checks to see if update is run
	 *
	 * @return void
	 */
	public function runUpdate() {
		$current_version = get_option( $this->name, 0 );

		// we've done this for the update already
		if ( Plugin::version() === $current_version ) {
			return;
		}

		// delete update transient to check for pro upgrade
		$this->deleteTransients();

		// update version
		update_option( $this->name, Plugin::version() );
	}

	/**
	 * Delete upgrade transients by key
	 *
	 * @return void
	 */
	public function deleteTransients() {
		foreach ( $this->getTransientsWithPrefix( 'presto_player_check_for_plugin_update' ) as $key ) {
			delete_site_transient( $key );
		}
	}

	/**
	 * Gets transients with a specific prefix.
	 *
	 * @param string $prefix
	 * @return array
	 */
	public function getTransientsWithPrefix( $prefix ) {
		global $wpdb;

		$prefix = $wpdb->esc_like( '_site_transient_' . $prefix );
		$sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
		$keys   = $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ), ARRAY_A );

		if ( is_wp_error( $keys ) ) {
			return array();
		}

		// Remove '_transient_' from the option name.
		return array_map(
			function ( $key ) {
				return ltrim( $key['option_name'], '_transient_' );
			},
			$keys
		);
	}
}
