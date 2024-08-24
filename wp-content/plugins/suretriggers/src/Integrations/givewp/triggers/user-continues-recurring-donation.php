<?php
/**
 * GiveWPUserContinuesRecurringDonation.
 * php version 5.6
 *
 * @category GiveWPUserContinuesRecurringDonation
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GiveWP\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use Give_Subscription;

if ( ! class_exists( 'GiveWPUserContinuesRecurringDonation' ) ) :

	/**
	 * GiveWPUserContinuesRecurringDonation
	 *
	 * @category GiveWPUserContinuesRecurringDonation
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class GiveWPUserContinuesRecurringDonation {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'GiveWP';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'givewp_user_continues_recurring_donation';

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
				'label'         => __( 'User Continues Recurring Donation', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'give_recurring_record_payment',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param object $payment Payment.
		 * @param int    $parent_payment_id Parent Payment ID.
		 * @param int    $amount Amount.
		 * @param int    $transaction_id Transaction ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $payment, $parent_payment_id, $amount, $transaction_id ) {
			if ( ! class_exists( 'Give_Subscription' ) ) {
				return;
			}
			if ( is_object( $payment ) && property_exists( $payment, 'ID' ) ) {
				$subscription_id = get_post_meta( $payment->ID, 'subscription_id', true );
				$subscription    = new Give_Subscription( $subscription_id );
				
				if ( is_object( $subscription ) && property_exists( $subscription, 'form_id' ) ||
					method_exists( $subscription, 'get_total_payments' ) ) {
					$give_form_id = $subscription->form_id;

					$total_payment = $subscription->get_total_payments();

					$context['form_id']      = $give_form_id;
					$context['subscription'] = $subscription;

					if ( $total_payment > 1 && 'active' === (string) $subscription->status ) {
						AutomationController::sure_trigger_handle_trigger(
							[
								'trigger' => $this->trigger,
								'context' => $context,
							]
						);
					}
				}
			}
		}

	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	GiveWPUserContinuesRecurringDonation::get_instance();

endif;
