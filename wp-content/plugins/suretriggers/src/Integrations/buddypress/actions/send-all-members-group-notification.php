<?php
/**
 * SendAllMembersGroupNotification.
 * php version 5.6
 *
 * @category SendAllMembersGroupNotification
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
 * SendAllMembersGroupNotification
 *
 * @category SendAllMembersGroupNotification
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendAllMembersGroupNotification extends AutomateAction {

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
	public $action = 'send_members_group_notification';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send all members of a group a notification', 'suretriggers' ),
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

		$sender               = $selected_options['wp_user_email'];
		$group_id             = $selected_options['bp_public_group'];
		$notification_content = $selected_options['notification_content'];
		$notification_link    = $selected_options['notification_link'];
		$members_ids          = [];

		if ( empty( $sender ) || ! is_email( $sender ) ) {
			throw new Exception( 'Invalid sender email.' );
		}

		if ( function_exists( 'groups_get_group_members' ) ) {
			$members = groups_get_group_members(
				[
					'group_id'       => $group_id,
					'per_page'       => 999999,
					'type'           => 'last_joined',
					'exclude_banned' => true,
				]
			);

			$sender_user = get_user_by( 'email', $sender );
			
			if ( isset( $members['members'] ) ) {
				if ( function_exists( 'bp_notifications_add_notification' ) ) {
					foreach ( $members['members'] as $member ) {
						if ( function_exists( 'bp_core_current_time' ) ) {
							if ( $sender_user ) {
								$sender_id       = $sender_user->ID;
								$notification_id = bp_notifications_add_notification(
									[
										'user_id'          => $member->ID,
										'secondary_item_id' => $sender_id,
										'component_name'   => 'suretriggers',
										'component_action' => 'suretriggers_bp_notification',
										'date_notified'    => bp_core_current_time(),
										'is_new'           => 1,
										'allow_duplicate'  => true,
									]
								);
								if ( is_wp_error( $notification_id ) ) {
									throw new Exception( $notification_id->get_error_message() );
								} else {
		
									// Add the link.
									if ( ! empty( $notification_link ) ) {
										$notification_content = '<a href="' . esc_url( $notification_link ) . '" title="' . esc_attr( wp_strip_all_tags( $notification_content ) ) . '">' . ( $notification_content ) . '</a>';
									}
		
									// Adding meta for notification display on front-end.
									if ( function_exists( 'bp_notifications_update_meta' ) ) {
										bp_notifications_update_meta( $notification_id, 'st_notification_content', $notification_content );
										bp_notifications_update_meta( $notification_id, 'st_notification_link', $notification_link );
									}

									$context['sender'] = WordPress::get_user_context( $user_id );
									if ( function_exists( 'bp_notifications_get_notification' ) && function_exists( 'bp_notifications_get_meta' ) ) {
										$notification            = bp_notifications_get_notification( $notification_id );
										$notification_meta       = bp_notifications_get_meta( $notification_id );
										$context['notification'] = array_merge( get_object_vars( $notification ), $notification_meta );
									}
									
									$context['group_members'] = $members;
									return $context;
								}
							}
						}
					}
				}
			} else {
				throw new Exception( 'No members found in group.' );
			}
		} else {
			throw new Exception( 'BuddyPress notification module is not active.' );
		}
	}
}

SendAllMembersGroupNotification::get_instance();
