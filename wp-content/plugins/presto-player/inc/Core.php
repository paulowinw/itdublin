<?php

namespace PrestoPlayer;

class Core {

	/**
	 * The singleton instance.
	 *
	 * @var Core
	 */
	private static $instance;

	/**
	 * Retrieves (and if necessary creates) the API instance. Should not be called outside of plugin set-up.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param  Core $instance Only used for plugin initialization. Don't ever pass a value in user code.
	 *
	 * @return void
	 *
	 * @throws \BadMethodCallException Thrown when Avatar_Privacy_Core::set_instance after plugin initialization.
	 */
	public static function set_instance( Core $instance ) {
		if ( null === self::$instance ) {
			self::$instance = $instance;
		} else {
			// throw new \BadMethodCallException(__METHOD__ . ' called more than once.');
		}
	}


	/**
	 * Retrieves the plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @throws \BadMethodCallException Thrown when Avatar_Privacy_Core::get_instance is called before plugin initialization.
	 *
	 * @return Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			throw new \BadMethodCallException( __METHOD__ . ' called without prior plugin intialization.' );
		}

		return self::$instance;
	}
}
