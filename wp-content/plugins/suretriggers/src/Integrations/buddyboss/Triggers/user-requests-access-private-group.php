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

namespace SureTriggers\Integrations\BuddyBoss\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

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
		public $trigger = 'bb_user_requests_access_private_group';

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
				'label'         => __( 'User Requests Access Private Group', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'groups_membership_requested',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int   $user_id ID of the user requesting membership.
		 * @param array $admins Array of group admins.
		 * @param int   $group_id ID of the group being requested to.
		 * @param int   $request_id ID of the request.
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $admins, $group_id, $request_id ) {

			if ( ! function_exists( 'groups_get_group' ) ) {
				return;
			}

			$context = WordPress::get_user_context( $user_id );
			$avatar  = get_avatar_url( $user_id );
			$group   = groups_get_group( $group_id );

			$context['avatar_url']        = ( $avatar ) ? $avatar : '';
			$context['group_id']          = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
			$context['group_name']        = ( property_exists( $group, 'name' ) ) ? $group->name : '';
			$context['group_description'] = ( property_exists( $group, 'description' ) ) ? $group->description : '';

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
					'context'    => $context,
				]
			);
		}
	}

	UserRequestsAccessPrivateGroup::get_instance();
endif;
