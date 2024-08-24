<?php
/**
 * UserRoleChange.
 * php version 5.6
 *
 * @category UserRoleChange
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\UltimateMember\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserRoleChange' ) ) :

	/**
	 * UserRoleChange
	 *
	 * @category UserRoleChange
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserRoleChange {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'UltimateMember';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_role_change';

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
				'label'         => __( 'User Role Change', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'set_user_role',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $user_id User ID.
		 * @param string $role Role.
		 * @param string $old_roles Old Role.
		 * @return void
		 */
		public function trigger_listener( $user_id, $role, $old_roles ) {
			
			$context         = WordPress::get_user_context( $user_id );
			$context['role'] = $role;
			
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
	UserRoleChange::get_instance();

endif;
