<?php
/**
 * SendNotificationToAllMembersOfGroup.
 * php version 5.6
 *
 * @category SendNotificationToAllMembersOfGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * SendNotificationToAllMembersOfGroup
 *
 * @category SendNotificationToAllMembersOfGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendNotificationToAllMembersOfGroup extends AutomateAction {

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
	public $action = 'bb_send_notification_to_all_members_of_group';

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
			'label'    => __( 'Send Notification To All Members Of Group', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, '_action_listener' ],
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
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $selected_options['sender_user'] ) || ! is_email( $selected_options['sender_user'] ) ) {
			throw new Exception( 'Invalid email.' );
		}

		$sender_id = email_exists( $selected_options['sender_user'] );

		if ( false === $sender_id ) {
			throw new Exception( 'User with email ' . $selected_options['sender_user'] . ' does not exists .' );
		}
		$group_id             = $selected_options['bb_group']['value'];
		$notification_content = $selected_options['bb_notification_content'];
		$notification_link    = $selected_options['bb_notification_link'];
		$context              = [];
		if ( function_exists( 'groups_get_group_members' ) ) {
			$members = groups_get_group_members(
				[
					'group_id'       => $group_id,
					'page'           => 1,
					'per_page'       => 999999,
					'type'           => 'last_joined',
					'exclude_banned' => true,
				]
			);
			if ( isset( $members['members'] ) ) {

				if ( function_exists( 'bp_notifications_add_notification' ) ) {

					foreach ( $members['members'] as $member ) {
						$context['member_ids'][]    = $member->ID;
						$context['member_emails'][] = $member->user_email;
						$notification_id            = bp_notifications_add_notification(
							[
								'user_id'           => $member->ID,
								'item_id'           => $group_id,
								'secondary_item_id' => $sender_id,
								'component_name'    => 'suretriggers',
								'component_action'  => 'sure-triggers_bb_notification',
								'date_notified'     => bp_core_current_time(),
								'is_new'            => 1,
								'allow_duplicate'   => true,
							]
						);
						// Adding meta for notification display on front-end.
						bp_notifications_update_meta( $notification_id, 'st_notification_content', $notification_content );
						bp_notifications_update_meta( $notification_id, 'st_notification_link', $notification_link );
					}
					return $context;
				}
			}
		}

		throw new Exception( SURE_TRIGGERS_ACTION_ERROR_MESSAGE );
	}
}

SendNotificationToAllMembersOfGroup::get_instance();
