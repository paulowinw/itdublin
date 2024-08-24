<?php
/**
 * UpdateUserAcfValue.
 * php version 5.6
 *
 * @category UpdateUserAcfValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * UpdateUserAcfValue
 *
 * @category UpdateUserAcfValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdateUserAcfValue extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'AdvancedCustomFields';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'update_user_acf_value';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Update User Value', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$field_name  = $selected_options['field_id'];
		$field_value = $selected_options['meta_value'];
		$user_id     = 'user_' . $selected_options['user_id'];

		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			throw new Exception( 'Advanced Custom Fields update_field() function not found.' );
		}

		if ( is_array( json_decode( $field_value, true ) ) ) {
			$field_value = json_decode( $field_value, true );
		}
		$response_array = [];
		if ( update_field( $field_name, $field_value, $user_id ) ) {
			$response_array[ $field_name ] = get_field( $field_name, $user_id, true );
			$response_array['user']        = WordPress::get_user_context( $selected_options['user_id'] );
		}
		return $response_array;
	}
}

UpdateUserAcfValue::get_instance();
