<?php
/**
 * UserMembershipStatusChanged.
 * php version 5.6
 *
 * @category UserMembershipStatusChanged
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceMemberships\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserMembershipStatusChanged' ) ) :

	/**
	 * UserMembershipStatusChanged
	 *
	 * @category UserMembershipStatusChanged
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserMembershipStatusChanged {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WoocommerceMemberships';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wc_user_membership_status_changed';

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
				'label'         => __( "User's membership status is changed", 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'wc_memberships_user_membership_status_changed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param object $user_membership_id User Membership ID.
		 * @param array  $old_status Old Status.
		 * @param array  $new_status New Status.
		 *
		 * @return void
		 */
		public function trigger_listener( $user_membership_id, $old_status, $new_status ) {

			if ( ! function_exists( 'wc_memberships_get_user_membership' ) ) {
				return;
			}
			$membership_plan = wc_memberships_get_user_membership( $user_membership_id );
			if ( 0 === $membership_plan->user_id ) {
				return;
			}
			$context['membership_plan']      = $membership_plan->plan_id;
			$context['new_status']           = $new_status;
			$context['old_status']           = $old_status;
			$context['membership_plan_name'] = $membership_plan->plan->name;
			$context['user']                 = WordPress::get_user_context( $membership_plan->user_id );
			
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
	UserMembershipStatusChanged::get_instance();

endif;
