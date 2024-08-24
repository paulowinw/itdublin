<?php
/**
 * WfRemoveTagFromUser.
 * php version 5.6
 *
 * @category WfRemoveTagFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPFusion\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Exception;

/**
 * WfRemoveTagFromUser
 *
 * @category WfRemoveTagFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WfRemoveTagFromUser extends AutomateAction {

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
	public $action = 'wf_remove_tag_from_user';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove Tag From User', 'suretriggers' ),
			'action'   => 'wf_remove_tag_from_user',
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
		$tag     = $selected_options['tag'];
		$user_id = $selected_options['wp_user_email'];

		if ( ! function_exists( 'wp_fusion' ) ) {
			return false;
		}

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );

			if ( $user ) {
				$user_id    = $user->ID;
				$contact_id = wp_fusion()->user->get_contact_id( $user_id, true );

				if ( false === $contact_id ) {
					wp_fusion()->user->user_register( $user_id );
				}
				
				$tag          = wp_fusion()->user->get_tag_id( $tag );
				$current_tags = wp_fusion()->user->get_tags( $user_id );

				if ( in_array( $tag, $current_tags, true ) ) {
					wp_fusion()->user->remove_tags( [ $tag ], $user_id );
				}

				$response = [
					wp_fusion()->user->get_tags( $user_id, true ),
					WordPress::get_user_context( $user_id ),
				];
				return $response;
			} else {
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'Please enter valid user.', 'suretriggers' ),
				];
				return $error;
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];
			return $error;
		}
	}

}

WfRemoveTagFromUser::get_instance();
