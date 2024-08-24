<?php
/**
 * AutomateAction.
 * php version 5.6
 *
 * @category AutomateAction
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations;

use Exception;

/**
 * AutomateAction
 *
 * @category AutomateAction
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
abstract class AutomateAction {

	/**
	 * Error data.
	 *
	 * @var null
	 */
	public $data = null;

	/**
	 * Action name.
	 *
	 * @var null|string
	 */
	public $action = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'sure_trigger_register_action', [ $this, 'register' ] );
		add_action( 'sure_trigger_action_' . $this->action, [ $this, 'action_listener' ], 10, 4 );

	}

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	abstract public function register( $actions ); //phpcs:ignore WordPressVIPMinimum.Hooks.AlwaysReturnInFilter.AbstractMethod

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 *
	 * @return void 
	 */
	abstract public function _action_listener( $user_id, $automation_id, $fields, $selected_options);

	/**
	 * Failed function callback.
	 *
	 * @param string $method method name.
	 * @param array  $args method arguments.
	 * @return false|void
	 * @throws Exception Excepotion.
	 */
	public function __call( $method, $args ) {
		// Allow support for only action_listener callback.
		if ( 'action_listener' !== $method ) {
			return;
		}

		// Copy and remove the context from function parameters.
		$context = isset( $args[4] ) ? $args[4] : '';
		unset( $args[4] );
			// Convert select field array values in to single value array.
			$temp_arr = [];
		if ( is_array( $args[3] ) ) {
			foreach ( $args[3] as $key => $val ) {
				if ( is_array( $val ) ) {
					$temp_arr[ $key ] = isset( $val['value'] ) ? $val['value'] : $val;
				} else {
					$temp_arr[ $key ] = $val;
				}
			}

			$args[3] = $temp_arr;
		}

		if ( isset( $args[3]['wp_user_email'] ) ) {
			$args[0] = ap_get_user_id_from_email( $args[3]['wp_user_email'] );

			if ( empty( $args[0] ) ) {
				$args[0] = 0;
			}
		}

			$method = '_' . $method;
		try {
			$status = $this->$method( ...$args );
		} catch ( Exception $e ) {
			throw new Exception( $e->getMessage() );
		}
		

		return $status;
	}

	/**
	 * Check required fields.
	 *
	 * @param array $fields template field array.
	 * @param array $selected_options admin selected option fields.
	 * @return bool
	 */
	public function check_required_fields( $fields, $selected_options ) {
		foreach ( $fields as $field ) {
			if ( 'test-action' === $field['type'] ) {
				continue;
			}

			$fieldname = isset( $selected_options[ $field['name'] ] ) ? $selected_options[ $field['name'] ] : false;

			if ( isset( $field['validationProps'] ) && ( empty( $fieldname ) && '0' !== $fieldname ) ) {
				$this->set_error(
					[
						'msg' => __( 'Required field is missing: ', 'suretriggers' ) . $field['name'],
					]
				);
				return false;
			}
		}
		return true;
	}

	/**
	 * Set the error before returning the false.
	 *
	 * @param array|object|null $data error data.
	 *
	 * @return void
	 */
	public function set_error( $data ) {
		$this->data = $data;
	}
}


