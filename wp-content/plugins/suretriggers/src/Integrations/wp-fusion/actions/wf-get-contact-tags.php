<?php
/**
 * WfGetContactTags.
 * php version 5.6
 *
 * @category WfGetContactTags
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPFusion\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * WfGetContactTags
 *
 * @category WfGetContactTags
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WfGetContactTags extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPFusion';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wf_get_contact_tags';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Contact Tags', 'suretriggers' ),
			'action'   => 'wf_get_contact_tags',
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
	 * 
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$contact_id = $selected_options['contact_id'];

		if ( ! function_exists( 'wp_fusion' ) ) {
			return false;
		}
		$wpfusion_api = wp_fusion()->crm->get_tags( $contact_id );
		if ( is_wp_error( $wpfusion_api ) ) {
			$error_message = $wpfusion_api->get_error_message();
			$response      = [
				'status'  => 'error',
				'message' => $error_message,
			];
		} else {
			if ( $wpfusion_api ) {
				$response = [
					'status' => 'success',
					'tags'   => $wpfusion_api,
				];
			} else {
				$response = [
					'status'  => 'error',
					'message' => esc_html__( 'No tags found for the given contact ID.', 'suretriggers' ),
				];
			}
		}

		return $response;
	}

}

WfGetContactTags::get_instance();
