<?php
/**
 * BMPrivateMessageToUser.
 * php version 5.6
 *
 * @category BMPrivateMessageToUser
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
 * BMPrivateMessageToUser
 *
 * @category BMPrivateMessageToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class BMPrivateMessageToUser extends AutomateAction {

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
	public $action = 'bm_private_message_to_user';

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
			'label'    => __( 'Send Private Message', 'suretriggers' ),
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

		if ( ! function_exists( 'Better_Messages' ) ) {
			return [];
		}

		if ( empty( $selected_options['receiver_user'] ) ) { 
			throw new Exception( 'Invalid receiver email.' );
		}

		$sender_id   = email_exists( $selected_options['sender_user'] );
		$receiver_id = email_exists( $selected_options['receiver_user'] );

		if ( false === $sender_id ) {
			throw new Exception( 'User with email ' . $selected_options['sender_user'] . ' does not exists .' );
		}
		if ( false === $receiver_id ) {
			throw new Exception( 'User with email ' . $selected_options['receiver_user'] . ' does not exists .' );
		}

		$message_subject    = $selected_options['message_subject'];
		$bm_message_content = $selected_options['bm_message_content'];
		$unique_tag         = $selected_options['unique_tag'];
		
		$data = [
			'sender_id'  => $sender_id,
			'recipients' => $receiver_id,
			'subject'    => $message_subject,
			'content'    => $bm_message_content,
			'return'     => 'message_id',
			'error_type' => 'wp_error',
		];

		if ( ! empty( $unique_tag ) ) {
			$user_ids = [ $sender_id, $receiver_id ];

			$thread_id = Better_Messages()->functions->get_unique_conversation_id( $user_ids, $unique_tag, $message_subject );

			$data = [
				'thread_id'  => $thread_id,
				'sender_id'  => $sender_id,
				'content'    => $bm_message_content,
				'return'     => 'message_id',
				'error_type' => 'wp_error',
			];
		}

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

BMPrivateMessageToUser::get_instance();
