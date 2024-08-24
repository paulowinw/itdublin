<?php
/**
 * CouponCodeRedeemed.
 * php version 5.6
 *
 * @category CouponCodeRedeemed
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPress\Triggers;

use MeprTransaction;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\MemberPress\MemberPress;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'CouponCodeRedeemed' ) ) :

	/**
	 * CouponCodeRedeemed
	 *
	 * @category CouponCodeRedeemed
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class CouponCodeRedeemed {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'MemberPress';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'mepr-coupon-code-redeemed';

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
				'label'         => __( 'Coupon Code Redeemed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [
					'mepr-event-transaction-completed',
				],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 * This will trigger for both recurring and non-recurring transactions.
		 *
		 * @param object $event event.
		 *
		 * @return void
		 */
		public function trigger_listener( $event ) {
			$transaction = $event->get_data();
			if ( empty( $transaction->coupon() ) ) {
				return;
			}
			$context              = array_merge(
				WordPress::get_user_context( $transaction->user_id ),
				MemberPress::get_membership_context( $transaction )
			);
			$context['coupon_id'] = $transaction->coupon()->ID;
			$context['coupon']    = get_post( $transaction->coupon()->ID );
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
	CouponCodeRedeemed::get_instance();

endif;
