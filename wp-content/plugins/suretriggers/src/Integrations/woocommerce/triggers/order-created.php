<?php
/**
 * OrderCreated.
 * php version 5.6
 *
 * @category OrderCreated
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

if ( ! class_exists( 'OrderCreated' ) ) :

	/**
	 * OrderCreated
	 *
	 * @category OrderCreated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class OrderCreated {

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
		public $trigger = 'wc_order_created';

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
				'label'         => __( 'Order created', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_checkout_order_processed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
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
			$order = wc_get_order( $order_id );
			
			if ( ! $order ) {
				return;
			}

			$user_id = $order->get_customer_id();
			$context = array_merge(
				WooCommerce::get_order_context( $order_id ),
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

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	OrderCreated::get_instance();

endif;
