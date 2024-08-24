<?php
/**
 * OrderPaid.
 * php version 5.6
 *
 * @category OrderPaid
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Woocommerce\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'OrderPaid' ) ) :

	/**
	 * OrderPaid
	 *
	 * @category OrderPaid
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class OrderPaid {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WooCommerce';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wc_order_paid';

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
				'label'         => __( 'Order Paid', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [
					'woocommerce_order_status_completed',
					'woocommerce_payment_complete',
				],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int $order_id order ID.
		 *
		 * @return void
		 */
		public function trigger_listener( $order_id ) {
			if ( ! $order_id ) {
				return;
			}
	
			$order = wc_get_order( $order_id );
	
			if ( ! $order instanceof \WC_Order ) {
				return;
			}

			if ( 'woocommerce_order_status_completed' === (string) current_action() || 'woocommerce_payment_complete' === (string) current_action() ) {
				if ( 'completed' !== $order->get_status() ) {
					return;
				}
			}
	
			$payment_method = $order->get_payment_method();
	
			if ( empty( $payment_method ) ) {
				return;
			}

			$user_id      = $order->get_customer_id();
			$order_detail = WooCommerce::get_order_context( $order_id );
			if ( is_array( $order_detail ) ) {
				$context = array_merge(
					$order_detail,
					WordPress::get_user_context( $user_id )
				);
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
	OrderPaid::get_instance();

endif;
