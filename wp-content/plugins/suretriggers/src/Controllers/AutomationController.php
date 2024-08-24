<?php
/**
 * AutomationController.
 * php version 5.6
 *
 * @category AutomationController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use SureTriggers\Traits\SingletonLoader;

/**
 * AutomationController
 *
 * @category AutomationController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AutomationController {

	use SingletonLoader;

	/**
	 * Trigger handler.
	 *
	 * @param array $trigger_data trigger data.
	 */
	public static function sure_trigger_handle_trigger( $trigger_data ) {
		// Calll rest API.
		return RestController::get_instance()->trigger_listener( $trigger_data );
	}

	/**
	 * Find the next node of automation on the basis of current node id.
	 *
	 * @param array $automation automation.
	 * @param int   $id node id.
	 * @return mixed|null
	 */
	public static function find_next_node( $automation, $id ) {
		if ( is_array( $automation ) && isset( $automation['rules'] ) ) {
			$rules = $automation['rules'];
			foreach ( $rules as $rule ) {

				if ( isset( $rule['parentId'] ) && $id === $rule['parentId'] ) {
					return $rule;
				}

				if ( isset( $rule['rules'] ) ) {
					foreach ( $rule['rules'] as $inner_rules ) {

						if ( isset( $inner_rules['parentId'] ) && $id === $inner_rules['parentId'] ) {
							return $inner_rules;
						}
					}
				}
			}
		}

		return null;
	}

	/**
	 * Register trigger listener.
	 *
	 * @return string
	 */
	public function register_trigger_listener() {
		$events                 = OptionController::get_option( 'triggers', [] );
		$test_trigger_transient = OptionController::get_option( 'test_triggers', [] );

		if ( empty( $events ) && empty( $test_trigger_transient ) ) {
			return;
		}

		if ( empty( $events ) && ! empty( $test_trigger_transient ) ) {
			$events = $test_trigger_transient;
		}

		if ( ! empty( $events ) && ! empty( $test_trigger_transient ) ) {
			$events = array_merge( $events, $test_trigger_transient );
		}

		foreach ( $events as $trigger ) {
			self::register_trigger( $trigger );
		}
	}

	/**
	 * Register a given trigger.
	 *
	 * @param array $trigger trigger.
	 * @return bool
	 */
	public static function register_trigger( $trigger ) {
		if ( ! isset( $trigger['trigger'] ) || ! isset( $trigger['integration'] ) ) {
			return;
		}

		$integration         = $trigger['integration'];
		$trigger_name        = $trigger['trigger'];
		$registered_triggers = EventController::get_instance()->triggers;

		// If Event is not registered but used in automation then continue.
		if ( ! isset( $registered_triggers[ $integration ][ $trigger_name ] ) ) {
			return false;
		}

		$event = $registered_triggers[ $integration ][ $trigger_name ];

		$action = isset( $event['common_action'] ) ? $event['common_action'] : $event['action'];

		if ( ! is_array( $action ) ) {
			add_action( $action, $event['function'], $event['priority'], $event['accepted_args'] );
		} else {
			foreach ( $action as $action_name ) {
				add_action( $action_name, $event['function'], $event['priority'], $event['accepted_args'] );
			}
		}

		return true;
	}
}

AutomationController::get_instance();
