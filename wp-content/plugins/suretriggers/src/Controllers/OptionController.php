<?php
/**
 * OptionController.
 * php version 5.6
 *
 * @category OptionController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'OptionController' ) ) :

	/**
	 * OptionController
	 *
	 * @category OptionController
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class OptionController {

		use SingletonLoader;

		/**
		 * Option key.
		 *
		 * @var string
		 */
		public static $option = 'suretrigger_options';

		/**
		 * Defaults.
		 *
		 * @var array
		 */
		public static $defaults = [];

		/**
		 * Options.
		 *
		 * @var array|false|void
		 */
		public static $options = [];

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			self::$options = get_option( self::$option, [] );
		}

		/**
		 * Get option wrapper.
		 *
		 * @param string $key key.
		 * @return mixed|string
		 */
		public static function get_option( $key ) {
			return isset( self::$options[ $key ] ) ? self::$options[ $key ] : '';
		}

		/**
		 * Set option wrapper.
		 *
		 * @param string       $key $key.
		 * @param string|array $value value.
		 *
		 * @return void
		 */
		public static function set_option( $key, $value ) {
			self::$options[ $key ] = $value;
			update_option( self::$option, self::$options, false );
		}
	}

	OptionController::get_instance();

endif;




