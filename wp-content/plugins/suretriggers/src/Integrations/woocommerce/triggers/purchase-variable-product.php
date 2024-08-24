<?php
/**
 * PurchaseVariableProduct.
 * php version 5.6
 *
 * @category PurchaseVariableProduct
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

/**
 * PurchaseVariableProduct
 *
 * @category PurchaseVariableProduct
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class PurchaseVariableProduct {

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
	public $trigger = 'wc_purchase_variable_product';

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
			'label'         => __( 'User purchases a variable product with a variation selected', 'suretriggers' ),
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
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$user_id = $order->get_customer_id();

		$items              = $order->get_items();
		$product_variations = [];

		foreach ( $items as $item ) {
			$product_variations[] = $item->get_variation_id();
		}
		foreach ( $product_variations as $product_variation_id ) {
			$product_id = wp_get_post_parent_id( $product_variation_id );
			if ( $product_id ) {

				$context                         = array_merge(
					WooCommerce::get_product_context( $product_id ),
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);
				$context['product_variation_id'] = $product_variation_id;
				$context['product_variation']    = get_the_excerpt( $product_variation_id );
				$context['total_items_in_order'] = count( $items );

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

PurchaseVariableProduct::get_instance();
