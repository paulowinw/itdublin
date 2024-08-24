<?php
/**
 * WpcwUserCompletesModule.
 * php version 5.6
 *
 * @category WpcwUserCompletesModule
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPCourseware\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * WpcwUserCompletesModule
 *
 * @category WpcwUserCompletesModule
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WpcwUserCompletesModule {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPCourseware';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wpcw_user_completes_module';

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 *
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Completes Module', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'wpcw_user_completed_module',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 20,
			'accepted_args' => 3,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int    $user_id User ID.
	 * @param int    $unit_id Unit ID.
	 * @param object $parent Parent.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $unit_id, $parent ) {

		if ( empty( $user_id ) ) {
			return;
		}

		if ( property_exists( $parent, 'parent_module_id' ) ) {
			$module_id = $parent->parent_module_id;
			if ( function_exists( 'wpcw_get_module' ) ) {
				$module = wpcw_get_module( $module_id );
				if ( is_object( $module ) ) {
					$module = get_object_vars( $module );
				}
				$context = array_merge( WordPress::get_user_context( $user_id ), $module );
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger'    => $this->trigger,
						'wp_user_id' => $user_id,
						'context'    => $context,
					]
				);
			}
		}
	}
}

WpcwUserCompletesModule::get_instance();
