<?php
/**
 * CreateGroup.
 * php version 5.6
 *
 * @category CreateGroup
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
 * CreateGroup
 *
 * @category CreateGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateGroup extends AutomateAction {

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
	public $action = 'bp_create_group';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create a group', 'suretriggers' ),
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
		
		$title             = $selected_options['group_name'];
		$privacy_options   = $selected_options['bp_group_status'];
		$add_other_users   = $selected_options['add_user'];
		$grp_creator_email = $selected_options['group_creator_email'];

		// Creating a group.
		if ( function_exists( 'groups_create_group' ) ) {
			if ( is_email( $grp_creator_email ) ) {
				$grp_creator = get_user_by( 'email', $grp_creator_email );
	
				if ( $grp_creator ) {
					$grp_creator_id = $grp_creator->ID;
					$group          = groups_create_group(
						[
							'creator_id' => $grp_creator_id,
							'name'       => $title,
							'status'     => $privacy_options,
						]
					);
					if ( is_wp_error( $group ) ) {
						throw new Exception( $group->get_error_message() );
					} elseif ( ! $group ) {
						throw new Exception( 'There is an error on creating group.' );
					} else {
						// Adding other users.
						if ( ! empty( $add_other_users ) ) {
							foreach ( $add_other_users as $user_selector ) {
								$existing_user_id = false;
								// Parse the value as token.
								$user_selector_value = $user_selector['wp_user_email'];
								if ( ! empty( $user_selector_value ) ) {
									$existing_user = get_user_by( 'email', $user_selector_value );
									if ( $existing_user ) {
										$existing_user_id = $existing_user->ID;
									}
									if ( $existing_user_id ) {
										if ( function_exists( 'groups_join_group' ) ) {
											groups_join_group( $group, $existing_user_id );
										}
									}
								}
							}
						}
						if ( function_exists( 'bp_get_group' ) ) {
							$grp_context = bp_get_group( $group );
							if ( is_object( $grp_context ) ) {
								$grp_context = get_object_vars( $grp_context );
							}
							$context['group']         = $grp_context;
							$context['group_creator'] = WordPress::get_user_context( $grp_creator->ID );
							if ( function_exists( 'groups_get_group_members' ) ) {
								$members = groups_get_group_members( [ 'group_id' => $group ] );
								if ( ! empty( $members ) ) {
									foreach ( $members['members'] as $member ) {
										$context['group_member'][] = WordPress::get_user_context( $member->ID );
									}
								}
							}
							return $context;
						}
					}
				}
			}
		}
	}
}

CreateGroup::get_instance();
