<?php
/**
 * SubscribeUserInForum.
 * php version 5.6
 *
 * @category AddTopicInForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * SubscribeUserInForum
 *
 * @category AddTopicInForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SubscribeUserInForum extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'bbPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bbp_user_subscribe_in_forum';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Subscribe User In Forum', 'suretriggers' ),
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
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$user_id = $selected_options['wp_user_email'];
		$forums  = $selected_options['forum'];
		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );
			if ( $user ) {
				$user_id = $user->ID;
			} 
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}


		if ( function_exists( 'bbp_is_subscriptions_active' ) && bbp_is_subscriptions_active() === false ) {
			return;
		}
		$forum_ids = [];
		foreach ( $forums as $forum ) {
			$forum_ids[] = $forum['value'];

		}

		if ( function_exists( 'bbp_is_user_subscribed' ) && ! empty( $forum_ids ) ) {
			foreach ( $forum_ids as $forum_id ) {
				$is_subscription = bbp_is_user_subscribed( $user_id, $forum_id );
				$success         = false;

				if ( true === $is_subscription ) {
					throw new Exception( 'The user is already subscribed to the specified forum' );
				} elseif ( function_exists( 'bbp_add_user_subscription' ) ) {
					$success = bbp_add_user_subscription( $user_id, $forum_id );
					// Do additional subscriptions actions.
					do_action( 'bbp_subscriptions_handler', $success, $user_id, $forum_id, 'bbp_subscribe' );
				}

				if ( false === $success && false === $is_subscription ) {
					throw new Exception( 'There was a problem subscribing to that forum!' );
				} else {
					$context = [
						'user_email'  => $selected_options['wp_user_email'],
						'forum_title' => get_the_title( $forum_id ),
						'forum_link'  => get_the_permalink( $forum_id ),
					];
		
					return $context;
				}
			}
		}
	}
}

SubscribeUserInForum::get_instance();
