<?php
/**
 * UserRequestsAccessPrivateGroup.
 * php version 5.6
 *
 * @category UserRequestsAccessPrivateGroup
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

if ( ! class_exists( 'UserRequestsAccessPrivateGroup' ) ) :

	/**
	 * UserRequestsAccessPrivateGroup
	 *
	 * @category UserRequestsAccessPrivateGroup
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserRequestsAccessPrivateGroup {


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
		public $trigger = 'user_request_access_private_group';

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
				'label'         => __( 'A user requests access to a private group', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'groups_membership_requested',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 60,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $user_id User ID.
		 * @param object $admins Admins.
		 * @param int    $group_id Group ID.
		 * @param int    $request_id Request ID.
		 * @return void
		 */
		public function trigger_listener( $user_id, $admins, $group_id, $request_id ) {

			if ( function_exists( 'groups_get_group' ) ) {
				$group = groups_get_group( $group_id );
				if ( is_object( $group ) ) {
					$group = get_object_vars( $group );
				}
				$context['group']            = $group;
				$context['bp_private_group'] = $group_id;
				$context['user']             = WordPress::get_user_context( $user_id );
				$context['request']          = $request_id;
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserRequestsAccessPrivateGroup::get_instance();

endif;
