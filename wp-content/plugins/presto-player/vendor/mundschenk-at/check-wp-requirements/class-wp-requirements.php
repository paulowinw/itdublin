<?php
/**
 *  This file is part of mundschenk-at/check-wp-requirements.
 *
 *  Copyright 2014-2019 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 *  @package mundschenk-at/check-wp-requirements
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PrestoPlayer\Mundschenk;

/**
 * This class checks if the required runtime environment is available.
 *
 * Included checks:
 *    - PHP version
 *    - mb_string extension
 *    - UTF-8 encoding
 *
 * Note: All code must be executable on PHP 5.2.
 */
class WP_Requirements {

	/**
	 * The minimum requirements for running the plugins. Must contain:
	 *  - 'php'
	 *  - 'multibyte'
	 *  - 'utf-8'
	 *
	 * @var array A hash containing the version requirements for the plugin.
	 */
	private $install_requirements;

	/**
	 * The user-visible name of the plugin.
	 *
	 * @todo Should the plugin name be translated?
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The full path to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * The textdomain used for loading plugin translations.
	 *
	 * @var string
	 */
	private $textdomain;

	/**
	 * The base directory of the Check_WP_Requirements component (i.e. the equivalent of __DIR__).
	 *
	 * @var string
	 */
	private $base_dir;

	/**
	 * Sets up a new Mundschenk\WP_Requirements object.
	 *
	 * @param string $name         The plugin name.
	 * @param string $plugin_path  The full path to the main plugin file.
	 * @param string $textdomain   The text domain used for i18n.
	 * @param array  $requirements The requirements to check against.
	 */
	public function __construct( $name, $plugin_path, $textdomain, $requirements ) {
		$this->plugin_name = $name;
		$this->plugin_file = $plugin_path;
		$this->textdomain  = $textdomain;
		$this->base_dir    = \dirname( __FILE__ );

		$this->install_requirements = \wp_parse_args( $requirements, [
			'php'       => '5.2.0',
			'multibyte' => false,
			'utf-8'     => false,
		] );
	}

	/**
	 * Checks if all runtime requirements for the plugin are met.
	 *
	 * @return bool
	 */
	public function check() {
		$requirements_met = true;

		foreach ( $this->get_requirements() as $requirement ) {
			if ( ! empty( $this->install_requirements[ $requirement['enable_key'] ] ) && ! \call_user_func( $requirement['check'] ) ) {
				$notice           = $requirement['notice'];
				$requirements_met = false;
				break;
			}
		}

		if ( ! $requirements_met && ! empty( $notice ) && \is_admin() ) {
			// Load text domain to ensure translated admin notices.
			\load_plugin_textdomain( $this->textdomain );

			// Add admin notice.
			\add_action( 'admin_notices', $notice );
		}

		return $requirements_met;
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
		return [
			[
				'enable_key' => 'php',
				'check'      => [ $this, 'check_php_support' ],
				'notice'     => [ $this, 'admin_notices_php_version_incompatible' ],
			],
			[
				'enable_key' => 'multibyte',
				'check'      => [ $this, 'check_multibyte_support' ],
				'notice'     => [ $this, 'admin_notices_mbstring_incompatible' ],
			],
			[
				'enable_key' => 'utf-8',
				'check'      => [ $this, 'check_utf8_support' ],
				'notice'     => [ $this, 'admin_notices_charset_incompatible' ],
			],
		];
	}

	/**
	 * Deactivates the plugin.
	 */
	public function deactivate_plugin() {
		\deactivate_plugins( \plugin_basename( $this->plugin_file ) );
	}

	/**
	 * Checks if the PHP version in use is at least equal to the required version.
	 *
	 * @return bool
	 */
	protected function check_php_support() {
		return \version_compare( \PHP_VERSION, $this->install_requirements['php'], '>=' );
	}

	/**
	 * Checks if multibyte functions are supported.
	 *
	 * @return bool
	 */
	protected function check_multibyte_support() {
		return \function_exists( 'mb_strlen' )
			&& \function_exists( 'mb_strtolower' )
			&& \function_exists( 'mb_substr' )
			&& \function_exists( 'mb_detect_encoding' );
	}

	/**
	 * Checks if the blog charset is set to UTF-8.
	 *
	 * @return bool
	 */
	protected function check_utf8_support() {
		return 'utf-8' === \strtolower( \get_bloginfo( 'charset' ) );
	}

	/**
	 * Print 'PHP version incompatible' admin notice
	 */
	public function admin_notices_php_version_incompatible() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: target PHP version number 3: actual PHP version number */
			\__( 'The activated plugin %1$s requires PHP %2$s or later. Your server is running PHP %3$s. Please deactivate this plugin, or upgrade your server\'s installation of PHP.', $this->textdomain ),
			"<strong>{$this->plugin_name}</strong>",
			$this->install_requirements['php'],
			\PHP_VERSION
		);
	}

	/**
	 * Prints 'mbstring extension missing' admin notice
	 */
	public function admin_notices_mbstring_incompatible() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: mbstring documentation URL */
			\__( 'The activated plugin %1$s requires the mbstring PHP extension to be enabled on your server. Please deactivate this plugin, or <a href="%2$s">enable the extension</a>.', $this->textdomain ),
			"<strong>{$this->plugin_name}</strong>",
			/* translators: URL with mbstring PHP extension installation instructions */
			\__( 'http://www.php.net/manual/en/mbstring.installation.php', $this->textdomain )
		);
	}

	/**
	 * Prints 'Charset incompatible' admin notice
	 */
	public function admin_notices_charset_incompatible() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: current character encoding 3: options URL */
			\__( 'The activated plugin %1$s requires your blog use the UTF-8 character encoding. You have set your blogs encoding to %2$s. Please deactivate this plugin, or <a href="%3$s">change your character encoding to UTF-8</a>.', $this->textdomain ),
			"<strong>{$this->plugin_name}</strong>",
			\get_bloginfo( 'charset' ),
			'/wp-admin/options-reading.php'
		);
	}

	/**
	 * Shows an error message in the admin area.
	 *
	 * @param string $format ... An `sprintf` format string, followd by an unspecified number of optional parameters.
	 */
	protected function display_error_notice( $format ) {
		if ( \func_num_args() < 1 || empty( $format ) ) {
			return; // abort.
		}

		$args    = \func_get_args();
		$format  = \array_shift( $args );
		$message = \vsprintf( $format, $args );

		require "{$this->base_dir}/partials/requirements-error-notice.php";
	}
}
