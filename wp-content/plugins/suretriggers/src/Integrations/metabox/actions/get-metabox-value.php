<?php
/**
 * GetMetaBoxValue.
 * php version 5.6
 *
 * @category GetMetaBoxValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * GetMetaBoxValue
 *
 * @category GetMetaBoxValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GetMetaBoxValue extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MetaBox';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'get_metabox_value';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Meta Value', 'suretriggers' ),
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
		$object_id = $selected_options['object_id'];
		$field_id  = $selected_options['field_id'];

		if ( function_exists( 'rwmb_get_value' ) ) {
			$response = rwmb_get_value( $field_id, '', $object_id );
			if ( empty( $response ) ) {
				$response = [
					'response' => esc_attr__( 'No value found.', 'suretriggers' ),
				];
			} else {
				$response = [
					'meta_value' => $response,
				];
			}
		} else {
			$response = [
				'Error' => esc_attr__( 'Function rwmb_get_value not exists. Please make sure the Metabox plugin is installed and active.', 'suretriggers' ),
			];
		}
		return $response;
	}
}

GetMetaBoxValue::get_instance();
