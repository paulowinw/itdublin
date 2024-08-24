<?php
/**
 * UpdateOptionAcfValue.
 * php version 5.6
 *
 * @category UpdateOptionAcfValue
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
 * UpdateOptionAcfValue
 *
 * @category UpdateOptionAcfValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdateOptionAcfValue extends AutomateAction {

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
	public $action = 'update_option_acf_value';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Update Option Value', 'suretriggers' ),
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
		$post_id     = 'option';

		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			throw new Exception( 'Advanced Custom Fields update_field() function not found.' );
		}

		if ( is_array( json_decode( $field_value, true ) ) ) {
			$field_value = json_decode( $field_value, true );
		}
		$response_array = [];
		if ( update_field( $field_name, $field_value, $post_id ) ) {
			$response_array[ $field_name ] = get_field( $field_name, $post_id, true );
		}
		return $response_array;
	}
}

UpdateOptionAcfValue::get_instance();
