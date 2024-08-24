<?php
/**
 * UserPurchasesMembership.
 * php version 5.6
 *
 * @category UserPurchasesMembership
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
use MemberOrder;

if ( ! class_exists( 'UserPurchasesMembership' ) ) :

	/**
	 * UserPurchasesMembership
	 *
	 * @category UserPurchasesMembership
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserPurchasesMembership {


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
		public $trigger = 'user_purchases_membership';

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
				'label'         => __( 'A user purchases a membership', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'pmpro_after_checkout',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 2,
			];
			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int    $user_id User ID.
		 * @param object $morder Cancel Level.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $morder ) {

			if ( method_exists( $morder, 'getMembershipLevel' ) ) {
				$membership    = $morder->getMembershipLevel();
				$membership_id = $membership->id;
		
				$context['membership_id'] = $membership->id;
				$context['membership']    = $membership;
				$context['user']          = WordPress::get_user_context( $user_id );
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
	UserPurchasesMembership::get_instance();

endif;
