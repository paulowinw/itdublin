<?php
/**
 * SendFriendshipRequestUser.
 * php version 5.6
 *
 * @category SendFriendshipRequestUser
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
 * SendFriendshipRequestUser
 *
 * @category SendFriendshipRequestUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendFriendshipRequestUser extends AutomateAction {

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
	public $action = 'send_friendship_request_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send a friendship request to a user', 'suretriggers' ),
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
	 * @return bool|array 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$friend_userid = $selected_options['wp_user_email'];
		$sender_userid = $selected_options['wp_sender_email'];
		if ( is_email( $friend_userid ) && is_email( $sender_userid ) ) {
			$user        = get_user_by( 'email', $friend_userid );
			$sender_user = get_user_by( 'email', $sender_userid );
			if ( $sender_user ) {
				if ( $user ) {
					$sender_user_id = $sender_user->ID;
					$user_id        = $user->ID;
					
					if ( function_exists( 'friends_add_friend' ) ) {
						$send = friends_add_friend( $sender_user_id, $user_id );
						if ( false === $send ) {
							throw new Exception( 'We are unable to send friendship request to selected user.' );
						} else {
							$context['sender']   = WordPress::get_user_context( $sender_user_id );
							$context['receiver'] = WordPress::get_user_context( $user_id );
							return $context;
						}
					} else {
						throw new Exception( 'BuddyPress connection module is not active.' );
					}
				} else {
					throw new Exception( 'Receiver with the email provided not found.' );   
				}
			} else {
				// If there's no user found, return default message.
				throw new Exception( 'Sender with the email provided not found.' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

SendFriendshipRequestUser::get_instance();
