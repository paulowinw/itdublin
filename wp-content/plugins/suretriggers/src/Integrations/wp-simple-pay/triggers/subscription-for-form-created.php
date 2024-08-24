<?php
/**
 * SubscriptionForFormCreated.
 * php version 5.6
 *
 * @category SubscriptionForFormCreated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WpSimplePay\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'SubscriptionForFormCreated' ) ) :

	/**
	 * SubscriptionForFormCreated
	 *
	 * @category SubscriptionForFormCreated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class SubscriptionForFormCreated {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WpSimplePay';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wsp_subscription_for_form_created';

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
				'label'         => __( 'Subscription For Form Created', 'suretriggers' ),
				'action'        => 'wsp_subscription_for_form_created',
				'common_action' => 'simpay_webhook_subscription_created',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 20,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array  $type Stripe webhook event.
		 * @param object $object Stripe PaymentIntent.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $type, $object ) {
			
			if ( ! isset( $object->metadata->simpay_form_id ) ) {
				return;
			}
			$form_id = $object->metadata->simpay_form_id;

			if ( empty( $form_id ) ) {
				return;
			}

			if ( ! isset( $object->latest_invoice ) ) {
				return;
			}

			$invoice = $object->latest_invoice;
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$context['customer'] = $object->customer;

			if ( function_exists( 'simpay_get_form' ) ) {
				$form                    = simpay_get_form( $form_id );
				$context['subscription'] = $form->company_name;
			}
			$context['invoice'] = $invoice;
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$context['amount_due'] = $object->amount_due;
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$context['amount_paid'] = $object->amount_paid;
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$context['amount_remaining'] = $object->amount_remaining;

			$context['wp_simple_pay_form'] = $form_id;
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
	SubscriptionForFormCreated::get_instance();

endif;
