<?php
/**
 * SingletonLoader.
 * php version 5.6
 *
 * @category SingletonLoader
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Traits;

/**
 * Trait SingletonLoader
 *
 * @template SingletonLoader
 *
 * @category SingletonLoader
 * @package SureTriggers\Traits
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
trait SingletonLoader {

	/**
	 * Instance
	 *
	 * @access public
	 * @var    null|object
	 * @since  1.0.0
	 */

	public static $_instance;

	/**
	 * Initiator
	 *
	 * @return SingletonLoader|object|null
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

}
