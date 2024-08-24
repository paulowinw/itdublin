<?php
/**
 * SendMessageToUsers.
 * php version 5.6
 *
 * @category SendMessageToUsers
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
 * SendMessageToUsers
 *
 * @category SendMessageToUsers
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendMessageToUsers extends AutomateAction {

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
	public $action = 'bb_send_message_to_users';

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
			'label'    => __( 'Send Message', 'suretriggers' ),
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

		if ( empty( $selected_options['reciever_user'] ) ) { 
			throw new Exception( 'Invalid reciever email.' );
		}

		$reciever_users = explode( ',', $selected_options['reciever_user'] );
		$reciever_ids   = [];
		foreach ( $reciever_users as $key => $value ) {
			if ( ! is_email( trim( $value ) ) ) {
				throw new Exception( 'Invalid reciever email ' . trim( $value ) . '.' );
			} elseif ( ! email_exists( $value ) ) {
				throw new Exception( 'User with email ' . trim( $value ) . ' does not exists .' );
			} else {
				array_push( $reciever_ids, email_exists( $value ) );
			}
		}

		$sender_id = email_exists( $selected_options['sender_user'] );

		if ( false === $sender_id ) {
			throw new Exception( 'User with email ' . $selected_options['sender_user'] . ' does not exists .' );
		}

		$message_subject    = $selected_options['message_subject'];
		$bb_message_content = $selected_options['bb_message_content'];
		$context            = [];
		$args               = [
			'sender_id'  => absint( $sender_id ),
			'recipients' => $reciever_ids,
			'subject'    => do_shortcode( $message_subject ),
			'content'    => do_shortcode( $bb_message_content ),
			'error_type' => 'wp_error',
		];

		if ( function_exists( 'messages_new_message' ) ) {
			$send = messages_new_message( $args );
			if ( $send ) {
				$context = [
					'sender'     => $selected_options['sender_user'],
					'recipients' => $reciever_users,
					'subject'    => $message_subject,
					'content'    => $bb_message_content,
				];
				return $context;    
			} else {
				throw new Exception( 'Failed to send message' );
			}
		} else {
			throw new Exception( 'Failed to send message' );
		}
	}
}

SendMessageToUsers::get_instance();
