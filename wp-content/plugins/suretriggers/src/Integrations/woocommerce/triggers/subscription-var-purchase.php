<?php
/**
 * SubscriptionVarPurchase.
 * php version 5.6
 *
 * @category SubscriptionVarPurchase
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
use WC_Subscription;
use WC_Subscriptions_Product;

/**
 * SubscriptionVarPurchase
 *
 * @category SubscriptionVarPurchase
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SubscriptionVarPurchase {

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
	public $trigger = 'wc_purchase_sub_variation';

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
			'label'         => __( 'User purchases a variable subscription', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'woocommerce_subscription_payment_complete',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 *  Trigger listener
	 *
	 * @param obj $subscription WC_Subscription object.
	 *
	 * @return void
	 */
	public function trigger_listener( $subscription ) {
		if ( ! $subscription instanceof WC_Subscription ) {
			return;
		}

		$last_order_id = $subscription->get_last_order();

		if ( ! empty( $last_order_id ) && $last_order_id !== $subscription->get_parent_id() ) {
			return;
		}

		$user_id = wc_get_order( $last_order_id )->get_customer_id();
		$id      = $subscription->get_id();

		$items       = $subscription->get_items();
		$product_ids = [];
		$context     = [];
		foreach ( $items as $item ) {
			$product = $item->get_product();
			if ( class_exists( '\WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
				if ( $product->is_type( [ 'subscription', 'subscription_variation', 'variable-subscription' ] ) ) {
					$context = array_merge(
						WooCommerce::get_variable_subscription_product_context( $item, $last_order_id ),
						WordPress::get_user_context( $user_id )
					);
				}
			}
		}
		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

SubscriptionVarPurchase::get_instance();
