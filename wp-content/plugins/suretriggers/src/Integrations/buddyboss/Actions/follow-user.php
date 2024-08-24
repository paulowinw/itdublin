<?php
/**
 * FollowUser.
 * php version 5.6
 *
 * @category FollowUser
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
 * FollowUser
 *
 * @category FollowUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FollowUser extends AutomateAction {

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
	public $action = 'bb_follow_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Follow User', 'suretriggers' ),
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
		
		$initiator_friend = $selected_options['wp_initiator_user_email'];
		$follower_email   = $selected_options['follower_email'];
		if ( is_email( $follower_email ) && is_email( $initiator_friend ) ) {
			$initiator_friend_user = get_user_by( 'email', $initiator_friend );
			$user                  = get_user_by( 'email', $follower_email );
			if ( $initiator_friend_user ) {
				if ( $user ) {
					if ( $initiator_friend_user->ID == $user->ID ) {
						throw new Exception( 'User can not follow itself.' );
					}
					if ( function_exists( 'bp_start_following' ) || 
					( function_exists( 'bp_is_active' ) && bp_is_active( 'follow' ) && 
					function_exists( 'bp_follow_start_following' ) ) ) {
						$args = [
							'follower_id' => $user->ID,
							'leader_id'   => $initiator_friend_user->ID,
						];
						if ( function_exists( 'bp_is_active' ) && bp_is_active( 'follow' ) && function_exists( 'bp_follow_start_following' ) ) {
							$following = bp_follow_start_following( $args );
							if ( false == $following ) {
								throw new Exception( 'User is already following.' );
							}
						} elseif ( function_exists( 'bp_start_following' ) ) {
							$following = bp_start_following( $args );
							if ( false == $following ) {
								throw new Exception( 'User is already following.' );
							}
						}
						$context['initiator'] = WordPress::get_user_context( $initiator_friend_user->ID );
						$context['user']      = WordPress::get_user_context( $user->ID );
						return $context;
					}
				} else {
					// If there's no user found, return default message.
					throw new Exception( 'Follower User provided not found.' );
				}
			} else {
				// If there's no user found, return default message.
				throw new Exception( 'Inititator User with the email provided not found.' );
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}
}

FollowUser::get_instance();
