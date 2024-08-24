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

namespace SureTriggers\Integrations\BuddyBoss\Actions;

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
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_create_group';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Group', 'suretriggers' ),
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
		$add_other_users   = $selected_options['add_user_repeater'];
		$grp_creator_email = $selected_options['group_creator_email'];
		$group_type        = $selected_options['bp_group_type'];
		$add_user          = $selected_options['add_user'];

		if ( ! function_exists( 'groups_create_group' ) || ! function_exists( 'groups_join_group' ) || 
		! function_exists( 'groups_get_group' ) || ! function_exists( 'bp_groups_get_group_type' ) || 
			! function_exists( 'bp_get_group_cover_url' ) || ! function_exists( 'bp_get_group_avatar_url' ) ||
			! function_exists( 'groups_get_group_members' ) || ! function_exists( 'groups_get_invites' ) || ! function_exists( 'bp_groups_set_group_type' ) ) {
			return;
		}

		// Creating a group.
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
					// set group type.
					if ( isset( $group_type ) && ! empty( $group_type ) ) {
						bp_groups_set_group_type( $group, $group_type );
					}
					// Adding other users.
					if ( ! empty( $add_other_users ) ) {
						foreach ( $add_other_users as $key => $user_selector ) {
							$member       = $user_selector['metaKey']['name'];
							$member_value = $add_user[ $key ][ $member ];
							if ( ! empty( $member_value ) ) {
								$existing_user = get_user_by( 'email', $member_value );
								if ( $existing_user ) {
									$existing_user_id = $existing_user->ID;
									groups_join_group( $group, $existing_user_id );
								}
							}
						}
					}
					$group_data                     = groups_get_group( $group );
					$context['group_id']            = ( property_exists( $group_data, 'id' ) ) ? (int) $group_data->id : '';
					$context['group_name']          = ( property_exists( $group_data, 'name' ) ) ? $group_data->name : '';
					$context['group_description']   = ( property_exists( $group_data, 'description' ) ) ? $group_data->description : '';
					$current_types                  = (array) bp_groups_get_group_type( $group_data->id, false );
					$context['group_type']          = $current_types;
					$context['group_status']        = $group_data->status;
					$context['group_date_created']  = $group_data->date_created;
					$context['group_enabled_forum'] = $group_data->enable_forum;
					$context['group_cover_url']     = bp_get_group_cover_url( $group_data );
					$context['group_avatar_url']    = bp_get_group_avatar_url( $group_data );
					$context['group_creator']       = WordPress::get_user_context( $group_data->creator_id );
					$members                        = groups_get_group_members(
						[
							'group_id' => $group_data->id,
						]
					);
					foreach ( $members['members'] as $key => $member ) {
						$context['group_member'][ $key ] = WordPress::get_user_context( $member->ID );
					}
					$args        = [ 'item_id' => $group_data->id ];
					$invitations = groups_get_invites( $args );
					if ( ! empty( $invitations ) ) {
						foreach ( $invitations as $key => $invite ) {
							$context['invitation'][ $key ] = $invite;
						}
					}
					return $context;
				}
			} else {
				throw new Exception( 'Group Creator Not Found.' );
			}
		}
	}
}

CreateGroup::get_instance();
