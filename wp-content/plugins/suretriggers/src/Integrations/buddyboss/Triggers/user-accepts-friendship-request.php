<?php
/**
 * UserAcceptsFriendshipRequest.
 * php version 5.6
 *
 * @category UserAcceptsFriendshipRequest
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserAcceptsFriendshipRequest' ) ) :
	/**
	 * UserAcceptsFriendshipRequest
	 *
	 * @category UserAcceptsFriendshipRequest
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserAcceptsFriendshipRequest {

		use SingletonLoader;

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BuddyBoss';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_accepts_friendship';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers triggers.
		 *
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'User Accepts Request', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'friends_friendship_accepted',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int $id id.
		 * @param int $initiator_id initiator user id.
		 * @param int $friend_id friend user id.
		 * @param int $friendship friendship.
		 *
		 * @return void
		 */
		public function trigger_listener( $id, $initiator_id, $friend_id, $friendship ) {
			$context = WordPress::get_user_context( $initiator_id );

			$friend_context = WordPress::get_user_context( $friend_id );

			$avatar = get_avatar_url( $initiator_id );

			$context['avatar_url'] = ( $avatar ) ? $avatar : '';

			$context['friend_id']         = $friend_id;
			$context['friend_first_name'] = $friend_context['user_firstname'];
			$context['friend_last_name']  = $friend_context['user_lastname'];
			$context['friend_email']      = $friend_context['user_email'];

			$friend_avatar                = get_avatar_url( $friend_id );
			$context['friend_avatar_url'] = $friend_avatar;

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserAcceptsFriendshipRequest::get_instance();
endif;
