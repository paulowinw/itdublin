<?php
/**
 * CreateNewOrder.
 * php version 5.6
 *
 * @category CreateNewOrder
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Woocommerce\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Traits\SingletonLoader;

/**
 * CreateNewOrder
 *
 * @category CreateNewOrder
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateNewOrder extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WooCommerce';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wc_create_new_order';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create a new order for the user', 'suretriggers' ),
			'action'   => 'wc_create_new_order',
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 *
	 * @return object|array|null
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$user_id = ap_get_user_id_from_email( $selected_options['billing_email'] );
		$order   = wc_create_order();

		$order->set_customer_id( $user_id );

		$quantity = $selected_options['quantity'] ? $selected_options['quantity'] : 1;

		// add products.
		$product = wc_get_product( $selected_options['product_id'] );
		if ( $product && $product->is_type( 'variation' ) ) {
			if ( method_exists( $product, 'get_variation_attributes' ) ) {
				$variation_data = $product->get_variation_attributes();
				if ( method_exists( $order, 'add_product' ) ) {
					$order->add_product( $product, $quantity, $variation_data );
				}
			}
		} else {
			$order->add_product( $product, $quantity );
		}

		if ( ! empty( $selected_options['coupon_id'] ) ) {
			if ( method_exists( $order, 'apply_coupon' ) ) {
				$order->apply_coupon( $selected_options['coupon_id'] );
			}
		}

		// add billing and shipping addresses.
		$billing_address = [
			'first_name' => $selected_options['billing_first_name'],
			'last_name'  => $selected_options['billing_last_name'],
			'company'    => $selected_options['billing_company'],
			'country'    => $selected_options['billing_country'],
			'address_1'  => $selected_options['billing_address_1'],
			'address_2'  => $selected_options['billing_address_2'],
			'city'       => $selected_options['billing_city'],
			'state'      => $selected_options['billing_state'],
			'postcode'   => $selected_options['billing_zip_code'],
			'phone'      => $selected_options['billing_phone'],
			'email'      => $selected_options['billing_email'],
		];

		$shipping_address = [
			'first_name' => $selected_options['shipping_first_name'] ? $selected_options['shipping_first_name'] : $selected_options['billing_first_name'],
			'last_name'  => $selected_options['shipping_last_name'] ? $selected_options['shipping_last_name'] : $selected_options['billing_last_name'],
			'company'    => $selected_options['shipping_company'] ? $selected_options['shipping_company'] : $selected_options['billing_company'],
			'country'    => $selected_options['shipping_country'] ? $selected_options['shipping_country'] : $selected_options['billing_country'],
			'address_1'  => $selected_options['shipping_address_1'] ? $selected_options['shipping_address_1'] : $selected_options['billing_address_1'],
			'address_2'  => $selected_options['shipping_address_2'] ? $selected_options['shipping_address_2'] : $selected_options['billing_address_2'],
			'city'       => $selected_options['shipping_city'] ? $selected_options['shipping_city'] : $selected_options['billing_city'],
			'state'      => $selected_options['shipping_state'] ? $selected_options['shipping_state'] : $selected_options['billing_state'],
			'postcode'   => $selected_options['shipping_zip_code'] ? $selected_options['shipping_zip_code'] : $selected_options['billing_zip_code'],
			'phone'      => $selected_options['shipping_phone'] ? $selected_options['shipping_phone'] : $selected_options['billing_phone'],
			'email'      => $selected_options['shipping_email'] ? $selected_options['shipping_email'] : $selected_options['billing_email'],
		];

		$order->set_address( $billing_address, 'billing' );
		$order->set_address( $selected_options['shipping_billing_address'] ? $billing_address : $shipping_address, 'shipping' );

		// add payment method.
		$order->set_payment_method( $selected_options['payment_method'] );
		$order->set_payment_method_title( $selected_options['payment_method_title'] );

		// order status.
		$order->set_status( $selected_options['status'] );

		// calculate and save.
		$order->calculate_totals();
		$order->save();

		return WooCommerce::get_order_context( $order->get_id() );
	}
}

CreateNewOrder::get_instance();
