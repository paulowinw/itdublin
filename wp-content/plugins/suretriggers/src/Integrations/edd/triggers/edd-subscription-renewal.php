<?php
/**
 * EDDSubscriptionRenewal.
 * php version 5.6
 *
 * @category EDDSubscriptionRenewal
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EDD\Triggers;

use EDD_Payment;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\EDD\EDD;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'EDDSubscriptionRenewal' ) ) :

	/**
	 * EDDSubscriptionRenewal
	 *
	 * @category EDDSubscriptionRenewal
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class EDDSubscriptionRenewal {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'EDD';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'edd_subscription_renewal';

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
				'label'         => __( 'Subscription Renewal', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'edd_subscription_post_renew',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $subscription_id Subscription id.
		 * @param string $expiration expiration time.
		 * @param array  $subscription subscription time.
		 * @param int    $payment_id payment id.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $subscription_id, $expiration, $subscription, $payment_id ) {
			if ( ! class_exists( '\EDD_Payment' ) ) {
				return;
			}
			$payment = new EDD_Payment( $payment_id );

			if ( empty( $payment->cart_details ) ) {
				return;
			}
			$context = EDD::get_product_purchase_context( $payment );

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
	EDDSubscriptionRenewal::get_instance();

endif;
