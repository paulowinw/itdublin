<?php
/**
 * CreateAgent.
 * php version 5.6
 *
 * @category CreateAgent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LatePoint\Actions;

use OsAgentModel;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * CreateAgent
 *
 * @category CreateAgent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateAgent extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LatePoint';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lp_create_agent';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Agent', 'suretriggers' ),
			'action'   => 'lp_create_agent',
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
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'OsAgentModel' ) ) {
			throw new Exception( 'LatePoint plugin not installed.' );
		}

		$services = [];
		if ( isset( $selected_options['service_ids'] ) ) {
			foreach ( $selected_options['service_ids'] as $service ) {
				$services[ 'service_' . $service['value'] ] = [
					'location_1' => [
						'connected' => 'yes',
					],
				];
			}
		}

		if ( is_array( $selected_options['wp_user_id'] ) && ! empty( $selected_options['wp_user_id'] ) ) {
			$wp_user_id = $selected_options['wp_user_id']['value'];
		} else {
			$wp_user_id = isset( $selected_options['wp_user_id'] ) ? $selected_options['wp_user_id'] : '';
		}

		$agent_params = [
			'first_name'   => isset( $selected_options['first_name'] ) ? $selected_options['first_name'] : '',
			'last_name'    => isset( $selected_options['last_name'] ) ? $selected_options['last_name'] : '',
			'display_name' => isset( $selected_options['display_name'] ) ? $selected_options['display_name'] : '',
			'email'        => isset( $selected_options['email'] ) ? $selected_options['email'] : '',
			'phone'        => isset( $selected_options['phone'] ) ? $selected_options['phone'] : '',
			'status'       => isset( $selected_options['status'] ) ? $selected_options['status'] : 'active',
			'wp_user_id'   => $wp_user_id,
			'extra_emails' => isset( $selected_options['extra_emails'] ) ? $selected_options['extra_emails'] : '',
			'extra_phones' => isset( $selected_options['extra_phones'] ) ? $selected_options['extra_phones'] : '',
			'title'        => isset( $selected_options['title'] ) ? $selected_options['title'] : '',
			'bio'          => isset( $selected_options['bio'] ) ? $selected_options['bio'] : '',
			'services'     => $services,
		];

		$agent = new OsAgentModel();
		$agent->set_data( $agent_params );

		if ( $agent->save() && ( empty( $agent_params['services'] ) || $agent->save_locations_and_services( $agent_params['services'] ) ) ) {
			unset( $selected_options['service_ids'] );
			unset( $selected_options['runningTestAction'] );

			$agent_services     = $agent->get_services();
			$new_agent_services = [];
			foreach ( $agent_services as $key => $value ) {
				$new_agent_services[] = [
					'id'   => $value->id,
					'name' => $value->name,
				];
			}
			$selected_options['services'] = $new_agent_services;
			if ( isset( $selected_options['switchableFields'] ) ) {
				unset( $selected_options['switchableFields'] );
			}
			return $selected_options;
		} else {
			$errors    = $agent->get_error_messages();
			$error_msg = isset( $errors[0] ) ? $errors[0] : 'Agent could not be created.';
			throw new Exception( $error_msg );
		}
	}

}

CreateAgent::get_instance();
