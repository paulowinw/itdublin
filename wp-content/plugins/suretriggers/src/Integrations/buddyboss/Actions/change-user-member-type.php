<?php
/**
 * ChangeUserMemberType.
 * php version 5.6
 *
 * @category ChangeUserMemberType
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * ChangeUserMemberType
 *
 * @category ChangeUserMemberType
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ChangeUserMemberType extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_change_user_member_type';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Change Member Profile Type', 'suretriggers' ),
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
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$user_id      = email_exists( $selected_options['wp_user_email'] );
		$profile_type = $selected_options['profile_type'];

		if ( empty( $user_id ) ) {
			return;
		}

		if ( ! function_exists( 'bp_set_member_type' ) ) {
			return;
		}

		$member_type = get_post_meta( $profile_type, '_bp_member_type_key', true );

		$type_post = get_post( $profile_type );
		if ( null !== $type_post ) {
			$member_type                   = $type_post->post_name;
			$selected_member_type_wp_roles = get_post_meta( $profile_type, '_bp_member_type_wp_roles', true );
	
			if ( bp_set_member_type( $user_id, $member_type ) ) {
				$bp_current_user = new \WP_User( $user_id );
				$is_admin_role   = false;
				foreach ( $bp_current_user->roles as $role ) {
					if ( 'administrator' === $role ) {
						$is_admin_role = true;
						break;
					}
				}
				
				if ( ! $is_admin_role ) {
					$bp_current_user->remove_role( $bp_current_user->roles[0] );
				}
				if ( is_array( $selected_member_type_wp_roles ) && is_string( $selected_member_type_wp_roles[0] ) ) {
					$bp_current_user->add_role( $selected_member_type_wp_roles[0] );
				}
				return $bp_current_user;
			}
		}
	}
}

ChangeUserMemberType::get_instance();
