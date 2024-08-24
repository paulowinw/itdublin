<?php
/**
 * UserMembershipPlanExpires.
 * php version 5.6
 *
 * @category UserMembershipPlanExpires
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

if ( ! class_exists( 'UserMembershipPlanExpires' ) ) :

	/**
	 * UserMembershipPlanExpires
	 *
	 * @category UserMembershipPlanExpires
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserMembershipPlanExpires {

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
		public $trigger = 'wc_user_membership_plan_expires';

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
				'label'         => __( "A user's access to a membership plan expires", 'suretriggers' ),
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
		 * @param object $membership_plan User Membership ID.
		 * @param array  $old_status Old Status.
		 * @param array  $new_status New Status.
		 *
		 * @return void
		 */
		public function trigger_listener( $membership_plan, $old_status, $new_status ) {

			if ( property_exists( $membership_plan, 'user_id' ) ) {
				if ( 0 === $membership_plan->user_id ) {
					// Its a logged in recipe and user ID is 0.
					return;
				}
			}

			if ( 'expired' !== $new_status ) {
				return;
			}

			if ( property_exists( $membership_plan, 'plan_id' ) ) {
				$membership_plan_type = get_post_meta( $membership_plan->plan_id, '_access_method', true );

				if ( 'purchase' === $membership_plan_type ) {
					if ( property_exists( $membership_plan, 'post' ) ) {
						$order_id = get_post_meta( $membership_plan->post->ID, '_order_id', true );
					}
				}

				$context['membership_plan'] = $membership_plan->plan_id;
			}
			$context['membership_plan_status'] = $new_status;
			if ( property_exists( $membership_plan, 'name' ) ) {
				$context['membership_plan_name'] = $membership_plan->name;
			}
			if ( property_exists( $membership_plan, 'user_id' ) ) {
				$context['user'] = WordPress::get_user_context( $membership_plan->user_id );
			}
			
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
	UserMembershipPlanExpires::get_instance();

endif;
