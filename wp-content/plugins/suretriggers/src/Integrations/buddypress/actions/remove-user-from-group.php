<?php
/**
 * RemoveUserFromGroup.
 * php version 5.6
 *
 * @category RemoveUserFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveUserFromGroup
 *
 * @category RemoveUserFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveUserFromGroup extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'remove_user_from_group';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove the user from a group', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$remove_from_bp_group = $selected_options['bp_group'];
		$remove_friend        = $selected_options['wp_user_email'];
		if ( is_email( $remove_friend ) ) {
			$user = get_user_by( 'email', $remove_friend );

			if ( $user ) {
				$user_id = $user->ID;
				
				if ( '-1' === $remove_from_bp_group ) {
					if ( function_exists( 'groups_get_user_groups' ) ) {
						$all_user_groups = groups_get_user_groups( $user_id );
						if ( ! empty( $all_user_groups['groups'] ) ) {
							foreach ( $all_user_groups['groups'] as $group_val ) {
								if ( function_exists( 'groups_leave_group' ) ) {
									if ( function_exists( 'groups_get_group' ) ) {
										$group = groups_get_group( $group_val );
										groups_leave_group( $group, $user_id );
										if ( is_object( $group ) ) {
											$group = get_object_vars( $group );
										}
										return array_merge(
											WordPress::get_user_context( $user_id ),
											$group
										);
									}
								}
							}
						}
					}
				} else {
					if ( function_exists( 'groups_leave_group' ) ) {
						if ( function_exists( 'groups_get_group' ) ) {
							$group = groups_get_group( $remove_from_bp_group );
							groups_leave_group( $group, $user_id );
							if ( is_object( $group ) ) {
								$group = get_object_vars( $group );
							}
							return array_merge(
								WordPress::get_user_context( $user_id ),
								$group
							);
						}
					}
				}
			} else {
				// If there's no user found, return default message.
				throw new Exception( 'User with the email provided not found.' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

RemoveUserFromGroup::get_instance();
