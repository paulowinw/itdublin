<?php
/**
 * UserConnectionRequestAccepted.
 * php version 5.6
 *
 * @category UserConnectionRequestAccepted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserConnectionRequestAccepted' ) ) :

	/**
	 * UserConnectionRequestAccepted
	 *
	 * @category UserConnectionRequestAccepted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserConnectionRequestAccepted {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BuddyPress';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_connection_request_accepted';

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
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( "A user's connection request is accepted", 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'friends_friendship_accepted',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $id ID.
		 * @param int    $initiator_user_id Initiator User ID.
		 * @param int    $friend_user_id Friendship User ID.
		 * @param object $friendship Friendship.
		 * @return void
		 */
		public function trigger_listener( $id, $initiator_user_id, $friend_user_id, $friendship ) {

			$context['initiator'] = WordPress::get_user_context( $initiator_user_id );
			$context['friend']    = WordPress::get_user_context( $friend_user_id );
			
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $initiator_user_id,
					'context'    => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserConnectionRequestAccepted::get_instance();

endif;
