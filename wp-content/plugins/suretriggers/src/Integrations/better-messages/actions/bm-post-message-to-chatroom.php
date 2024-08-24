<?php
/**
 * BMPostMessageToChatroom.
 * php version 5.6
 *
 * @category BMPostMessageToChatroom
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BetterMessages\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * BMPostMessageToChatroom
 *
 * @category BMPostMessageToChatroom
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class BMPostMessageToChatroom extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BetterMessages';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bm_post_message_to_chatroom';

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
			'label'    => __( 'Post Message to Chatroom', 'suretriggers' ),
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
			throw new Exception( 'Invalid sender email.' );
		}

		if ( ! function_exists( 'Better_Messages_Chats' ) || ! function_exists( 'Better_Messages' ) ) {
			return [];
		}

		$sender_id = email_exists( $selected_options['sender_user'] );
		if ( false === $sender_id ) {
			throw new Exception( 'User with email ' . $selected_options['sender_user'] . ' does not exists .' );
		}

		$message_subject    = $selected_options['message_subject'];
		$bm_message_content = $selected_options['bm_message_content'];

		if ( is_array( $selected_options['chatroom_id'] ) ) {
			$chatroom_id = $selected_options['chatroom_id']['value'];
		} else {
			$chatroom_id = $selected_options['chatroom_id'];
		}

		$chat    = \Better_Messages_Chats();
		$is_chat = $chat->is_chat_room( $chatroom_id );

		if ( ! $is_chat ) {
			throw new Exception( 'Invalid Chatroom.' );
		}

		$thread_id = $chat->get_chat_thread_id( $chatroom_id );
		
		// Returns true if user is participant or false if user is not participant.
		$is_participant = Better_Messages()->functions->is_user_participant( $thread_id, $sender_id );

		if ( ! $is_participant ) {
			$join = $chat->add_to_chat( $sender_id, $chatroom_id );
			if ( ! $join ) {
				throw new Exception( 'Specified sender could not join this chatroom.' );
			}
		}

		$data   = [
			'sender_id'  => $sender_id,
			'thread_id'  => $thread_id,
			'subject'    => $message_subject,
			'content'    => $bm_message_content,
			'return'     => 'message_id',
			'error_type' => 'wp_error',
		];
		$result = Better_Messages()->functions->new_message( $data );

		// If there was an error, it'll be logged in action log with an error message.
		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			throw new Exception( $error_message );
		} else {
			return Better_Messages()->functions->get_message( $result );
		}
	}
}

BMPostMessageToChatroom::get_instance();
