<?php
/**
 * File Integrations Controller.
 * php version 5.6
 *
 * @package SureTrigger
 */

namespace SureTriggers\Controllers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class IntegrationsController
 *
 * @package SureTriggers\Controllers
 */
class IntegrationsController {

	use SingletonLoader;

	/**
	 * Get registered pluggable variables.
	 *
	 * @return mixed|void
	 */
	public static function get_registered_pluggable_variables() {
		return apply_filters( 'sure_trigger_register_pluggable_variables', [] );
	}

	/**
	 * Registering integration classes
	 *
	 * @param string $class Integration class name.
	 *
	 * @return void
	 */
	public static function register( $class ) {
		add_filter(
			'sure_trigger_integrations',
			function ( $integrations_classes ) use ( $class ) {

				$obj = new $class();

				$integrations_classes[ $obj->get_id() ] = $obj;

				return $integrations_classes;
			},
			99
		);
	}

	/**
	 * Include activated integrations events files
	 *
	 * @return void
	 */
	public static function load_event_files() {
		/**
		 * Get integration class and check if the class is activated
		 * If yes, then include those classes event files.
		 */
		$integrations_classes = self::get_integrations();
		foreach ( $integrations_classes as $class ) {

			if ( $class->is_enabled() ) {
				$reflector   = new ReflectionClass( $class );
				$event_files = new RecursiveDirectoryIterator( dirname( $reflector->getFileName() ) );
				foreach ( new RecursiveIteratorIterator( $event_files ) as $filename => $file ) {
					if ( $file->isFile() ) {
						require_once $filename;
					}
				}
			}
		}
	}

	/**
	 * Get all integration classes
	 *
	 * @return Integrations[]
	 */
	public static function get_integrations() {
		return apply_filters( 'sure_trigger_integrations', [] );
	}

	/**
	 * Get integration which plugins are activated.
	 *
	 * @return array
	 */
	public static function get_activated_integrations() {
		$integrations_classes = self::get_integrations();
		$allowed_integrations = [];

		foreach ( $integrations_classes as $integration => $class ) {
			if ( 'WordPress' === $integration ) {
				continue;
			}

			if ( $class->is_enabled() ) {
				$allowed_integrations [] = $integration;
			}
		}

		return $allowed_integrations;
	}

	/**
	 * Verify child integration
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_REST_Response
	 */
	public function child_integration_verify( $request ) {
		$plugin_url = admin_url( 'plugins.php' );
		if ( $request->get_param( 'integration_plugin' ) ) {
			$is_plugin_active = false;

			$plugin = $request->get_param( 'integration_plugin' );
			
			$integration_class_name = $plugin['key'];

			$fully_qualified_class_name = "\SureTriggers\Integrations\\$integration_class_name\\$integration_class_name";

			if ( class_exists( $fully_qualified_class_name ) ) {
				$class_obj        = new $fully_qualified_class_name();
				$is_plugin_active = $class_obj->is_plugin_installed();
			}

			if ( $is_plugin_active ) {
				return RestController::success_message( 'Integration is verified and has all necessary plugins installed.' );
			} else {
				return RestController::error_message( sprintf( 'To use %1s integration, you must have installed and activated %1$s on your <a class="text-app-primary" target="_blank" href="%2$s"> WordPress website</a>.', $plugin['name'], $plugin_url ), 200 );
			}
		}

		return RestController::error_message( 'No integration details provided.', 200 );
	}

}
