<?php
/**
 * SendPrivateMessageUser.
 * php version 5.6
 *
 * @category SendPrivateMessageUser
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
 * SendPrivateMessageUser
 *
 * @category SendPrivateMessageUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendPrivateMessageUser extends AutomateAction {

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
	public $action = 'send_private_message_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send a private message to the user', 'suretriggers' ),
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
		
		$sender          = $selected_options['wp_user_email'];
		$receiver        = $selected_options['to_user_email'];
		$subject         = $selected_options['message_subject'];
		$message_content = $selected_options['message_content'];

		if ( empty( $sender ) || ! is_email( $sender ) ) {
			throw new Exception( 'Invalid sender email.' );
		}

		if ( empty( $receiver ) || ! is_email( $receiver ) ) { 
			throw new Exception( 'Invalid reciever email.' );
		}

		if ( empty( $message_content ) ) { 
			throw new Exception( 'Please enter message content.' );
		}

		$user      = get_user_by( 'email', $sender );
		$receivers = get_user_by( 'email', $receiver );

		// Attempt to send the message.
		if ( function_exists( 'messages_new_message' ) ) {
			if ( $user && $receivers ) {
				$sender_id   = $user->ID;
				$receiver_id = $receivers->ID;
				$send        = messages_new_message(
					[
						'sender_id'  => $sender_id,
						'recipients' => [ $receiver_id ],
						'subject'    => $subject,
						'content'    => $message_content,
						'error_type' => 'wp_error',
					]
				);
				if ( is_wp_error( $send ) ) {
					throw new Exception( $send->get_error_message() );
				} else {
					$context = [
						'sender'     => $sender,
						'recipients' => $receiver,
						'subject'    => $subject,
						'content'    => $message_content,
					];
					return $context; 
				}
			}
		} else {
			throw new Exception( 'BuddyPress message module is not active.' );
		}
	}
}

SendPrivateMessageUser::get_instance();
