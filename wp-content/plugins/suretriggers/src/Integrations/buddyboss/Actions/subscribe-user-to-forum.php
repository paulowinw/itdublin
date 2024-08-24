<?php
/**
 * SubscribeUserToForum.
 * php version 5.6
 *
 * @category SubscribeUserToForum
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
 * SubscribeUserToForum
 *
 * @category SubscribeUserToForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SubscribeUserToForum extends AutomateAction {

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
	public $action = 'bb_subscribe_user_to_forum';

	use SingletonLoader;

	/**
	 * Register.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Subscribe User to Forum', 'suretriggers' ),
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
	 * @param array $selected_options selected options.
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $selected_options['user'] ) || ! is_email( $selected_options['user'] ) ) {
			throw new Exception( 'Invalid email.' );
		}

		$user_id = email_exists( $selected_options['user'] );

		if ( false === $user_id ) {
			throw new Exception( 'User with email ' . $selected_options['user'] . ' does not exists .' );
		}

		if ( bbp_is_subscriptions_active() === false ) {
			throw new Exception( 'Members are not allowed to subscribe to forums. Please contact site admin.' );
		}

		$forum_id = $selected_options['bb_forum'];
		$forum    = bbp_get_forum( $forum_id );
		if ( empty( $forum ) ) {
			throw new Exception( 'Invalid forum.' );
		}

		bbp_add_user_subscription( $user_id, $forum_id );
		$subscribed = bbp_add_user_forum_subscription( $user_id, $forum_id );
		if ( $subscribed ) {
			return $forum;
		}
		throw new Exception( SURE_TRIGGERS_ACTION_ERROR_MESSAGE );
	}
}

SubscribeUserToForum::get_instance();
