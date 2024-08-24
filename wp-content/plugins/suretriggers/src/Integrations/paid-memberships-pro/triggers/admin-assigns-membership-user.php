<?php
/**
 * AdminAssignsMembershipUser.
 * php version 5.6
 *
 * @category AdminAssignsMembershipUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PaidMembershipsPro\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use PMPro_Membership_Level;

if ( ! class_exists( 'AdminAssignsMembershipUser' ) ) :

	/**
	 * AdminAssignsMembershipUser
	 *
	 * @category AdminAssignsMembershipUser
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AdminAssignsMembershipUser {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'PaidMembershipsPro';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'admin_assigns_membership_user';

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
				'label'         => __( 'An admin assigns a membership level to a user', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'pmpro_after_change_membership_level',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 3,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $level_id ID of the level changed to.
		 * @param int $user_id ID of the user changed.
		 * @param int $cancel_level ID of the level being cancelled if specified.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $level_id, $user_id, $cancel_level ) {

			if ( class_exists( 'PMPro_Membership_Level' ) ) {
				$membership_level = new PMPro_Membership_Level();
				$level_data       = $membership_level->get_membership_level( $level_id );
				$context['level'] = $level_data;
			}

			$context['user']          = WordPress::get_user_context( $user_id );
			$context['membership_id'] = $level_id;

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
	AdminAssignsMembershipUser::get_instance();

endif;
