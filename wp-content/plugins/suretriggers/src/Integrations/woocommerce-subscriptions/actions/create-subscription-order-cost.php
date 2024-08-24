<?php
/**
 * CreateSubscriptionOrderCost.
 * php version 5.6
 *
 * @category CreateSubscriptionOrderCost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceSubscriptions\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use WC_Subscriptions_Product;
use WC_Order;

/**
 * CreateSubscriptionOrderCost
 *
 * @category CreateSubscriptionOrderCost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateSubscriptionOrderCost extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WoocommerceSubscriptions';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wc_create_subscription_order_cost';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create a subscription order with a product', 'suretriggers' ),
			'action'   => 'wc_create_subscription_order_cost',
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param mixed $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @throws Exception Exception.
	 *
	 * @return object|array|null|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! function_exists( 'wc_create_order' ) || ! function_exists( 'wcs_create_subscription' ) || ! class_exists( 'WC_Subscriptions' ) ) {
			throw new Exception( '`wc_create_order` or `wcs_create_subscription` function is missing.' );
		}

		if ( ! class_exists( '\WC_Order' ) ) {
			return;
		}

		if ( ! class_exists( 'WC_Subscriptions_Product' ) ) {
			return;
		}

		$user_id = ap_get_user_id_from_email( $selected_options['billing_email'] );

		$quantity = $selected_options['quantity'] ? $selected_options['quantity'] : 1;

		if ( 'yes' == $selected_options['create_parent_order'] ) {
			// Create Order.
			$order = wc_create_order(
				[
					'status'      => 'wc-pending',
					'customer_id' => $user_id,
				] 
			);

			if ( ! $order instanceof \WC_Order ) {
				return;
			}

			if ( is_object( $order ) ) {
				$order = $order;
			}
			if ( isset( $selected_options['product_id'] ) ) {
				$product = wc_get_product( intval( $selected_options['product_id'] ) );
				if ( $product instanceof \WC_Product ) {
					$order->add_product( $product, intval( $quantity ) );
				}
			}

			// Setting order cost to entered order cost.
			$order->set_total( $selected_options['parent_order_cost'] );
			// Add billing and shipping addresses.
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

			// Setting status of order to selected status.
			$order->update_status( $selected_options['parent_order_status'] );
			$order->save();
			do_action( 'woocommerce_update_order', $order->get_id() );
			
		}
		if ( isset( $selected_options['product_id'] ) ) {
			$sub_args = [
				'customer_id'      => $user_id,
				'billing_period'   => WC_Subscriptions_Product::get_period( intval( $selected_options['product_id'] ) ),
				'billing_interval' => WC_Subscriptions_Product::get_interval( intval( $selected_options['product_id'] ) ),
			];
			if ( 'yes' == $selected_options['create_parent_order'] ) {
				if ( ! empty( $order ) ) {
					$sub_args['order_id'] = $order->get_id();
				}
			}
			$sub = wcs_create_subscription( $sub_args );
			
			if ( is_wp_error( $sub ) ) {
				if ( ! empty( $order ) ) {
					wp_delete_post( $order->get_id(), true );
				}
				throw new Exception( 'Failed to create a subscription.' );
			}

			$sub->add_product( wc_get_product( intval( $selected_options['product_id'] ) ), intval( $quantity ) );
			$sub->apply_coupon( $selected_options['coupon_code'] );
			$start_date = gmdate( 'Y-m-d H:i:s' );

			$trial_end_days = $selected_options['trial_end_days'];
			
			if ( '' != $trial_end_days ) {
				$now                 = strtotime( 'now' );
				$trial_end_timestamp = strtotime( "+$trial_end_days days", $now );

				if ( false !== $trial_end_timestamp ) {
					$trial_end_date     = gmdate( 'Y-m-d H:i:s', $trial_end_timestamp );
					$dates['trial_end'] = $trial_end_date;

					$trial_end_timestamp = strtotime( $trial_end_date );
					if ( false !== $trial_end_timestamp ) {
						$next_payment_date     = gmdate( 'Y-m-d H:i:s', strtotime( '+1 day', $trial_end_timestamp ) );
						$dates['next_payment'] = WC_Subscriptions_Product::get_expiration_date( intval( $selected_options['product_id'] ), $next_payment_date );
					}
				}

				$start_date = $sub->get_date_created();
				$end_date   = WC_Subscriptions_Product::get_expiration_date( intval( $selected_options['product_id'] ), $start_date );

				$dates['end'] = $end_date;
			} else {
				$dates = [
					'trial_end'    => WC_Subscriptions_Product::get_trial_expiration_date( intval( $selected_options['product_id'] ), $start_date ),
					'next_payment' => WC_Subscriptions_Product::get_first_renewal_payment_date( intval( $selected_options['product_id'] ), $start_date ),
					'end'          => WC_Subscriptions_Product::get_expiration_date( intval( $selected_options['product_id'] ), $start_date ),
				];
			}

			$sub->update_dates( $dates );
			$sub->update_status( $selected_options['status'] );
			$sub->calculate_totals();
		}

		if ( ! empty( $sub ) ) {
			$context['subscription'] = [
				'id'                => $sub->get_id(),
				'status'            => $sub->get_status(),
				'start_date'        => $sub->get_date_created(),
				'next_payment_date' => $sub->get_date( 'next_payment' ),
				'trial_end_date'    => $sub->get_date( 'trial_end' ),
				'end_date'          => $sub->get_date( 'end' ),
			];
			if ( 'yes' == $selected_options['create_parent_order'] ) {
				if ( ! empty( $order ) ) {
					$order_details = WooCommerce::get_order_context( $order->get_id() );
					if ( is_array( $order_details ) ) {
						return array_merge( $context, $order_details );
					}
				}
			} else {
				return $context;
			}
		} else {
			if ( ! empty( $order ) ) {
				return WooCommerce::get_order_context( $order->get_id() );
			}
		}
	}
}

CreateSubscriptionOrderCost::get_instance();
