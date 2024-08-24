<?php
/**
 * UserMembershipExpires.
 * php version 5.6
 *
 * @category UserMembershipExpires
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

if ( ! class_exists( 'UserMembershipExpires' ) ) :

	/**
	 * UserMembershipExpires
	 *
	 * @category UserMembershipExpires
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserMembershipExpires {


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
		public $trigger = 'user_membership_expires';

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
				'label'         => __( "A user's subscription to a membership expires", 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'pmpro_membership_post_membership_expiry',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 100,
				'accepted_args' => 2,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $user_id User ID.
		 * @param int $membership_id Membership ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $membership_id ) {
			global $wpdb;

			if ( empty( $user_id ) || empty( $membership_id ) ) {
				return;
			}

			$membership_level = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT name, description 
					FROM $wpdb->pmpro_membership_levels
					WHERE id = %d",
					$membership_id
				),
				OBJECT   
			);

			$context['user']             = WordPress::get_user_context( $user_id );
			$context['membership_level'] = $membership_level;
			$context['membership_id']    = $membership_id;

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
	UserMembershipExpires::get_instance();

endif;
