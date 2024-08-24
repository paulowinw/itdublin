<?php
/**
 * ChangeUserRoleToSpecific.
 * php version 5.6
 *
 * @category ChangeUserRoleToSpecific
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WordPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'ChangeUserRoleToSpecific' ) ) :


	/**
	 * ChangeUserRoleToSpecific
	 *
	 * @category ChangeUserRoleToSpecific
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class ChangeUserRoleToSpecific {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WordPress';

		/**
		 * Action name.
		 *
		 * @var string
		 */
		public $trigger = 'change_specific_role_to_specific';

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
		 * Register a action.
		 *
		 * @param array $triggers actions.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Role: A user\'s role changed from a specific role to a specific role', 'suretriggers' ),
				'action'        => 'change_specific_role_to_specific',
				'common_action' => 'set_user_role',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int    $user_id user id.
		 * @param string $role The new role.
		 * @param array  $old_roles An array of the user's previous roles.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $role, $old_roles ) {

			$context = WordPress::get_user_context( $user_id );

			foreach ( $old_roles as $old_role ) {

				$context['new_role'] = $role;
				$context['old_role'] = $old_role;

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	ChangeUserRoleToSpecific::get_instance();

endif;
