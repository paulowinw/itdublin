<?php

namespace PrestoPlayer;

use PrestoPlayer\Mundschenk\WP_Requirements;

class Requirements extends WP_Requirements {

	const REQUIREMENTS = array(
		'php'       => '7.3',
		'multibyte' => false,
		'utf-8'     => false,
		'wp'        => '5.6',
	);

	/**
	 * Creates a new requirements instance.
	 *
	 * @since 2.1.0 Parameter $plugin_file replaced with AVATAR_PRIVACY_PLUGIN_FILE constant.
	 */
	public function __construct() {
		parent::__construct( 'Presto Player', PRESTO_PLAYER_PLUGIN_FILE, 'presto-player', self::REQUIREMENTS );
	}

	/**
	 * Retrieves an array of requirement specifications.
	 *
	 * @return array {
	 *         An array of requirements checks.
	 *
	 *   @type string   $enable_key An index in the $install_requirements array to switch the check on and off.
	 *   @type callable $check      A function returning true if the check was successful, false otherwise.
	 *   @type callable $notice     A function displaying an appropriate error notice.
	 * }
	 */
	protected function get_requirements() {
		$requirements   = parent::get_requirements();
		$requirements[] = array(
			'enable_key' => 'wp',
			'check'      => array( $this, 'check_wp_support' ),
			'notice'     => array( $this, 'admin_notices_wp_incompatible' ),
		);

		return $requirements;
	}

	/**
	 * Checks for availability of the GD extension.
	 *
	 * @return bool
	 */
	protected function check_wp_support() {
		global $wp_version;
		return version_compare( $wp_version, '5.6', '>=' );
	}

	/**
	 * Prints 'WordPress Update' admin notice
	 *
	 * @return void
	 */
	public function admin_notices_wp_incompatible() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: WordPress update documentation URL */
			\__( 'The activated plugin %1$s requires WordPress 5.6 or higher. Please update WordPress.', 'presto-player' ),
			'<strong>Presto Player</strong>',
			/* translators: URL with WordPress installation instructions */
			\__( 'https://wordpress.org/support/article/updating-wordpress/', 'presto-player' )
		);
	}
}
