<?php
/**
 * StopFollowingUser.
 * php version 5.6
 *
 * @category StopFollowingUser
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
 * StopFollowingUser
 *
 * @category StopFollowingUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class StopFollowingUser extends AutomateAction {

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
	public $action = 'bb_stop_following_user';

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
			'label'    => __( 'Stop Following User', 'suretriggers' ),
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
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! function_exists( 'bp_is_active' ) ) {
			return;
		}
			
		$initiator_friend = $selected_options['wp_initiator_user_email'];
		$unfollower_email = $selected_options['user_email'];
		if ( is_email( $unfollower_email ) && is_email( $initiator_friend ) ) {
			$initiator_friend_user = get_user_by( 'email', $initiator_friend );
			$user                  = get_user_by( 'email', $unfollower_email );
			if ( $initiator_friend_user ) {
				if ( $user ) {
					if ( $initiator_friend_user->ID == $user->ID ) {
						throw new Exception( 'User can not follow itself.' );
					}
					if ( bp_is_active( 'moderation' ) ) {
						$args = [
							'follower_id' => $initiator_friend_user->ID,
							'leader_id'   => $user->ID,
						];
						if ( bp_is_active( 'follow' ) && function_exists( 'bp_follow_stop_following' ) ) {
							$following = bp_follow_stop_following( $args );
							if ( false == $following ) {
								throw new Exception( 'The Initiator User was not following member - ' . $unfollower_email . '. ' );
							}
						} elseif ( function_exists( 'bp_stop_following' ) ) {
							$following = bp_stop_following( $args );
							if ( false == $following ) {
								throw new Exception( 'The Initiator User was not following member - ' . $unfollower_email . '. ' );
							}
						}
						$context['follower'] = WordPress::get_user_context( $initiator_friend_user->ID );
						$context['leader']   = WordPress::get_user_context( $user->ID );
						return $context;
					} else {
						throw new Exception(
							'To un follow members, 
                        please activate the Moderation component.' 
						);
					}
				} else {
					throw new Exception( 'User to Un follow Not found.' );
				}
			} else {
				throw new Exception( 'User Not found.' );
			}
		} else {
			throw new Exception( 'Please enter valid email.' );
		}
	}
}

StopFollowingUser::get_instance();
