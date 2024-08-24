<?php
/**
 * CustomerTotalSpendReachSpecificAmount.
 * php version 5.6
 *
 * @category CustomerTotalSpendReachSpecificAmount
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WooCommerce\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use WC_Customer;

/**
 * CustomerTotalSpendReachSpecificAmount
 *
 * @category CustomerTotalSpendReachSpecificAmount
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CustomerTotalSpendReachSpecificAmount {

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
	public $trigger = 'wc_customer_total_spend_reach_specific_amount';

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
			'event_name'    => 'woocommerce_order_status_changed',
			'label'         => __( 'Customer Created', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'woocommerce_order_status_changed',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 3,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param string $order_id order ID.
	 * @param string $old_status old status.
	 * @param string $new_status new status.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $order_id, $old_status, $new_status ) {
		if ( ! $order_id ) {
			return;
		}

		if ( 'completed' !== $new_status ) {
			return;
		}

		$order = wc_get_order( $order_id );
			
		if ( ! $order ) {
			return;
		}

		if ( is_object( $order ) && method_exists( $order, 'get_customer_id' ) ) {
			$customer      = new WC_Customer( $order->get_customer_id() );
			$last_order    = $customer->get_last_order();
			$customer_data = [
				'id'            => $customer->get_id(),
				'email'         => $customer->get_email(),
				'first_name'    => $customer->get_first_name(),
				'last_name'     => $customer->get_last_name(),
				'username'      => $customer->get_username(),
				'last_order_id' => is_object( $last_order ) ? $last_order->get_id() : null,
				'order_count'   => $customer->get_order_count(),
				'total_spend'   => wc_format_decimal( $customer->get_total_spent(), 2 ),
			];
			if ( is_object( $last_order ) && method_exists( $last_order, 'get_date_created' ) ) {
				$created_date = $last_order->get_date_created();
				if ( is_object( $created_date ) && method_exists( $created_date, 'getTimestamp' ) ) {
					$last_order_date                  = $created_date->getTimestamp();
					$customer_data['created_at']      = $last_order_date;
					$customer_data['last_order_date'] = $last_order_date;
				}
			}
			$context = $customer_data;

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}
}

CustomerTotalSpendReachSpecificAmount::get_instance();
