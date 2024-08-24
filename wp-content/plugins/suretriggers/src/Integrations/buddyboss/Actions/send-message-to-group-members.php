<?php
/**
 * SendMessageToGroupMembers.
 * php version 5.6
 *
 * @category SendMessageToGroupMembers
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
 * SendMessageToGroupMembers
 *
 * @category SendMessageToGroupMembers
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendMessageToGroupMembers extends AutomateAction {

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
	public $action = 'bb_send_message_to_group_members';

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
			'label'    => __( 'Send Message to Group Members', 'suretriggers' ),
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
	 * @return array|void|string
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$sender_user_email = $selected_options['sender_user_email'];

		$group_id        = $selected_options['bb_group']['value'];
		$subject         = $selected_options['subject'];
		$message_content = $selected_options['message_content'];

		if ( ! function_exists( 'groups_get_group' ) ) {
			return [];
		} 

		if ( function_exists( 'groups_get_group_members' ) ) {
			$members = groups_get_group_members(
				[
					'group_id'       => $group_id,
					'per_page'       => -1,
					'type'           => 'last_joined',
					'exclude_banned' => true,
				]
			);
			if ( ! empty( $members['members'] ) ) {
				$members_ids = [];
				foreach ( $members['members'] as $member ) {
					array_push( $members_ids, $member->ID );
				}
				if ( is_email( $sender_user_email ) ) {
					$user = get_user_by( 'email', $sender_user_email );
					if ( $user ) {
						$sender_id = $user->ID;
					} else {
						throw new Exception( ' Sender user not found ' );
					}
				} else {
					throw new Exception( ' Please provide valid email exists. ' );
				}
				if ( $members_ids || $sender_id ) {
					// Attempt to send the message.
					$msg = [
						'sender_id'  => $sender_id,
						'recipients' => $members_ids,
						'subject'    => $subject,
						'content'    => $message_content,
						'error_type' => 'wp_error',
					];

					if ( function_exists( 'messages_new_message' ) ) {
						$send = messages_new_message( $msg );
						if ( is_wp_error( $send ) ) {
							$messages = $send->get_error_messages();
							return $messages;
						} else {
							$group   = groups_get_group( $group_id );
							$context = [
								'sender'  => WordPress::get_user_context( $sender_id ),
								'subject' => $subject,
								'content' => $message_content,
								'group'   => $group,
							];
							foreach ( $members_ids as $key => $member ) {
								$context['recipients'][ $key ] = WordPress::get_user_context( $member );
							}
							return $context;
						}
					}
				} else {
					throw new Exception( 'Group members not found.' );
				}
			} else {
				throw new Exception( 'Group members not found.' );
			}
		} else {
			throw new Exception( 'BuddyBoss message module is not active.' );
		}
	}
}

SendMessageToGroupMembers::get_instance();
