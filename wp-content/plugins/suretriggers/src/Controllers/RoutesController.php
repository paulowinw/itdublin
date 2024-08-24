<?php
/**
 * RoutesController.
 * php version 5.6
 *
 * @category AuthController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use SureTriggers\Traits\SingletonLoader;
use WP_REST_Server;

/**
 * RoutesController- Register all routes here.
 *
 * @category RoutesController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class RoutesController {

	use SingletonLoader;

	/**
	 * Initialise data.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Registe endpoint for Sass. 
	 *
	 * @return void
	 */
	public function register_endpoints() {
		$rest_controller_obj         = RestController::get_instance();
		$auth_controller_obj         = AuthController::get_instance();
		$globalsearch_controller_obj = GlobalSearchController::get_instance();
		$integration_controller_obj  = IntegrationsController::get_instance();

		// Register new triggers from SAAS.
		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'automation/triggers',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $rest_controller_obj, 'manage_triggers' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		// Execute respective integration action event.
		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'automation/action',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $rest_controller_obj, 'run_action' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		// Revoke acccess_token.
		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'connection/revoke',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $auth_controller_obj, 'revoke_connection' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'automation/global-search',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $globalsearch_controller_obj, 'global_search' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'connection/child-integration-verify',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $integration_controller_obj, 'child_integration_verify' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'connection/update',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $rest_controller_obj, 'connection_update' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'connection/disconnect',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $rest_controller_obj, 'connection_disconnect' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		// Test trigger.
		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'automation/test-trigger',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $rest_controller_obj, 'test_triggers' ],
				'permission_callback' => [ $rest_controller_obj, 'autheticate_user' ],
			]
		);

		register_rest_route(
			SURE_TRIGGERS_REST_NAMESPACE,
			'api-test',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => function () {
					wp_die( 'SureTriggers Says: API Working perfectly!' );
				},
				'permission_callback' => '__return_true',
			]
		);
	}
}

RoutesController::get_instance();
