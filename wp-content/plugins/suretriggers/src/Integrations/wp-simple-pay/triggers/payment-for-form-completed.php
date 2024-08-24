<?php
/**
 * PaymentForFormCompleted.
 * php version 5.6
 *
 * @category PaymentForFormCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WpSimplePay\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'PaymentForFormCompleted' ) ) :

	/**
	 * PaymentForFormCompleted
	 *
	 * @category PaymentForFormCompleted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PaymentForFormCompleted {


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
		public $trigger = 'wsp_payment_for_form_completed';

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
				'label'         => __( 'Payment For Form Completed', 'suretriggers' ),
				'action'        => 'wsp_payment_for_form_completed',
				'common_action' => 'simpay_webhook_payment_intent_succeeded',
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

			if ( isset( $object->customer, $object->amount, $object->description, $object->currency, $object->latest_charge, $object->payment_method ) ) {
				$context = [ 
					'wp_simple_pay_form' => $form_id, 
					'customer'           => $object->customer, 
					'amount'             => $object->amount, 
					'description'        => $object->description, 
					'metadata'           => $object->metadata, 
					'currency'           => $object->currency, 
					'latest_charge'      => $object->latest_charge, 
					'payment_method'     => $object->payment_method, 
				];
			} else {
				$context['wp_simple_pay_form'] = $form_id;
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
	PaymentForFormCompleted::get_instance();

endif;
