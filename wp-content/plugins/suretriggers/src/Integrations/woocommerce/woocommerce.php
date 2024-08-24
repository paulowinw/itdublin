<?php
/**
 * WooCommerce integration class file
 *
 * @package  SureTriggers
 * @since 1.0.0
 */

namespace SureTriggers\Integrations\WooCommerce;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use WC_Order;
use WC_Customer;

/**
 * Class WooCommerce
 *
 * @package SureTriggers\Integrations\WooCommerce
 */
class WooCommerce extends Integrations {

	use SingletonLoader;

	/**
	 * ID of the integration
	 *
	 * @var string
	 */
	protected $id = 'WooCommerce';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_created_customer', [ $this, 'woo_customer_created_trigger' ], 10, 3 );
		add_action( 'woocommerce_thankyou', [ $this, 'woo_customer_created_order_trigger' ], 10, 1 );
		parent::__construct();
	}

	/**
	 * On form submit.
	 *
	 * @param int    $customer_id        New customer (user) ID.
	 * @param array  $new_customer_data  Array of customer (user) data.
	 * @param string $password_generated The generated password for the account.
	 * @return void
	 */
	public function woo_customer_created_trigger( $customer_id, $new_customer_data, $password_generated ) {
		// Check if customer creation is happening during checkout.
		if ( isset( $_POST['woocommerce-process-checkout-nonce'] ) && ! empty( $_POST['woocommerce-process-checkout-nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Customer creation is happening during checkout, so return it.
			return;
		}
		$customer_query = get_users(
			[
				'fields'  => 'ID',
				'include' => [ $customer_id ],
			]
		);
		$results        = $customer_query;
		if ( ! empty( $results ) ) {
			$customer      = new WC_Customer( $results[0] );
			$last_order    = $customer->get_last_order();
			$customer_data = [
				'id'               => $customer->get_id(),
				'email'            => $customer->get_email(),
				'first_name'       => $customer->get_first_name(),
				'last_name'        => $customer->get_last_name(),
				'username'         => $customer->get_username(),
				'last_order_id'    => is_object( $last_order ) ? $last_order->get_id() : null,
				'orders_count'     => $customer->get_order_count(),
				'total_spent'      => wc_format_decimal( $customer->get_total_spent(), 2 ),
				'avatar_url'       => $customer->get_avatar_url(),
				'billing_address'  => [
					'first_name' => $customer->get_billing_first_name(),
					'last_name'  => $customer->get_billing_last_name(),
					'company'    => $customer->get_billing_company(),
					'address_1'  => $customer->get_billing_address_1(),
					'address_2'  => $customer->get_billing_address_2(),
					'city'       => $customer->get_billing_city(),
					'state'      => $customer->get_billing_state(),
					'postcode'   => $customer->get_billing_postcode(),
					'country'    => $customer->get_billing_country(),
					'email'      => $customer->get_billing_email(),
					'phone'      => $customer->get_billing_phone(),
				],
				'shipping_address' => [
					'first_name' => $customer->get_shipping_first_name(),
					'last_name'  => $customer->get_shipping_last_name(),
					'company'    => $customer->get_shipping_company(),
					'address_1'  => $customer->get_shipping_address_1(),
					'address_2'  => $customer->get_shipping_address_2(),
					'city'       => $customer->get_shipping_city(),
					'state'      => $customer->get_shipping_state(),
					'postcode'   => $customer->get_shipping_postcode(),
					'country'    => $customer->get_shipping_country(),
				],
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
			do_action( 'wc_customer_created_trigger', $context );
		}
	}

	/**
	 * On form submit.
	 *
	 * @param int $order_id        New customer (user) ID.
	 * @return void
	 */
	public function woo_customer_created_order_trigger( $order_id ) {
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
			
		if ( ! $order ) {
			return;
		}
		if ( is_object( $order ) && method_exists( $order, 'get_customer_id' ) ) {
			$customer = new WC_Customer( $order->get_customer_id() );
			if ( $customer->get_order_count() > 1 ) {
				return;
			}
			$last_order    = $customer->get_last_order();
			$customer_data = [
				'id'               => $customer->get_id(),
				'email'            => $customer->get_email(),
				'first_name'       => $customer->get_first_name(),
				'last_name'        => $customer->get_last_name(),
				'username'         => $customer->get_username(),
				'last_order_id'    => is_object( $last_order ) ? $last_order->get_id() : null,
				'orders_count'     => $customer->get_order_count(),
				'total_spent'      => wc_format_decimal( $customer->get_total_spent(), 2 ),
				'avatar_url'       => $customer->get_avatar_url(),
				'billing_address'  => [
					'first_name' => $customer->get_billing_first_name(),
					'last_name'  => $customer->get_billing_last_name(),
					'company'    => $customer->get_billing_company(),
					'address_1'  => $customer->get_billing_address_1(),
					'address_2'  => $customer->get_billing_address_2(),
					'city'       => $customer->get_billing_city(),
					'state'      => $customer->get_billing_state(),
					'postcode'   => $customer->get_billing_postcode(),
					'country'    => $customer->get_billing_country(),
					'email'      => $customer->get_billing_email(),
					'phone'      => $customer->get_billing_phone(),
				],
				'shipping_address' => [
					'first_name' => $customer->get_shipping_first_name(),
					'last_name'  => $customer->get_shipping_last_name(),
					'company'    => $customer->get_shipping_company(),
					'address_1'  => $customer->get_shipping_address_1(),
					'address_2'  => $customer->get_shipping_address_2(),
					'city'       => $customer->get_shipping_city(),
					'state'      => $customer->get_shipping_state(),
					'postcode'   => $customer->get_shipping_postcode(),
					'country'    => $customer->get_shipping_country(),
				],
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
			do_action( 'wc_customer_created_trigger', $context );
		}
	}

	/**
	 * Get product details context.
	 *
	 * @param object $item item.
	 * @param int    $order_id ID.
	 *
	 * @return array
	 */
	public static function get_variable_subscription_product_context( $item, $order_id ) {
		$product       = $item->get_product();
		$order_context = self::get_order_context( $order_id );
		$product_data  = $product->get_data();
		return array_merge( $order_context, $product_data );
	}

	/**
	 * Get product details context.
	 *
	 * @param int $product_id ID.
	 *
	 * @return array
	 */
	public static function get_product_context( $product_id ) {
		$product = wc_get_product( $product_id );
		return array_merge( [ 'product_id' => $product_id ], $product->get_data(), $product->get_attributes() );
	}

	/**
	 * Get product details context
	 *
	 * @param int $order_id order id.
	 *
	 * @return array|null
	 */
	public static function get_order_context( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}
		if ( $order instanceof WC_Order ) {
			$coupon_codes = [];
			$order_codes  = $order->get_coupon_codes();
			if ( ! empty( $order_codes ) ) {
				foreach ( $order_codes as $coupon_code ) {
					$coupon                         = new \WC_Coupon( $coupon_code );
					$data                           = $coupon->get_data();
					$coupon_detail['code_name']     = $coupon_code;
					$coupon_detail['discount_type'] = $coupon->get_discount_type();
					$coupon_detail['coupon_amount'] = $coupon->get_amount();
					$coupon_detail['meta_data']     = $data['meta_data'];
					$coupon_codes[]                 = $coupon_detail;
				}
			}
			
			$product_ids = [];
			$quantities  = [];
			$items       = $order->get_items();
			foreach ( $items as $item ) {
				$product_ids[] = $item->get_product_id();
				$quantities[]  = $item->get_quantity();
			}

			$discounts           = $order->get_items( 'discount' );
			$line_items_fee      = $order->get_items( 'fee' );
			$line_items_shipping = $order->get_items( 'shipping' );

			return array_merge(
				[ 'product_id' => $product_ids[0] ],
				$order->get_data(),
				[ 'coupons' => $coupon_codes ],
				[ 'products' => self::get_order_items_context_array( $items ) ],
				[ 'line_items' => self::get_order_items_context( $items ) ],
				[ 'quantity' => implode( ', ', $quantities ) ],
				[ 'discounts' => implode( ', ', $discounts ) ],
				[ 'line_items_fee' => implode( ', ', $line_items_fee ) ],
				[ 'line_items_shipping' => json_decode( implode( ', ', $line_items_shipping ) ) ]
			);
		} else {
			return [];
		}
	}

	/**
	 * Get order details context.
	 *
	 * @param str $order_id order_id.
	 *
	 * @return array|null
	 */
	public static function get_only_order_context( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return null;
		}
		return array_merge(
			$order->get_data()
		);
	}

	/**
	 * Get product details context.
	 *
	 * @param array $items items.
	 *
	 * @return array
	 */
	public static function get_order_items_context_array( $items ) {
		$new_items = [];
		foreach ( $items as $item ) {
			$item_data              = [];
			$item_data              = $item->get_data();
			$item_data['meta_data'] = $item->get_formatted_meta_data( '_', true );
			$new_items[]            = $item_data;
		}
		return $new_items;
	}

	/**
	 * Get product details context.
	 *
	 * @param array $items items.
	 *
	 * @return array
	 */
	public static function get_order_items_context( $items ) {
		$new_items = [];
		foreach ( $items as $item ) {
			$item_data = [];
			$item_data = $item->get_data();
			unset( $item_data['meta_data'] );
			$item_data['meta_data'] = $item->get_formatted_meta_data( '_', true );
			$new_items[]            = $item_data;
		}

		$product = [];

		foreach ( $new_items[0] as $item_key => $item_value ) {
			if ( 'meta_data' === $item_key ) {
				$product[ $item_key ] = self::loop_over_meta_item( $item_value );
			} else {
				$product[ $item_key ] = implode(
					', ',
					array_map(
						function ( $entry ) use ( $item_key ) {
							$ent = $entry[ $item_key ];

							$ent = self::loop_over_item( $ent );
							return $ent;
						},
						$new_items
					)
				);
			}
		}

		return $product;
	}

	/**
	 * Loop items
	 *
	 * @param array $items items.
	 *
	 * @return array
	 */
	public static function loop_over_meta_item( $items ) {
		$meta = [];
		foreach ( $items as $subitem ) {
			foreach ( $subitem as $key => $sub ) {
				$meta[ $key ] = implode(
					', ',
					array_map(
						function ( $entry ) use ( $key ) {
							$ent = $entry->$key;
							$ent = self::loop_over_item( $ent );
							return $ent;
						},
						$items
					)
				);
			}
		}
		return $meta;
	}

	/**
	 * Get product details context.
	 *
	 * @param array $item item.
	 *
	 * @return array
	 */
	public static function loop_over_item( $item ) {
		if ( is_array( $item ) || is_object( $item ) ) {
			foreach ( $item as $subitem ) {
				self::loop_over_item( $subitem );
			}
		} else {
			return $item;
		}
	}

	/**
	 * Get product details context.
	 *
	 * @param array $order order.
	 *
	 * @return array
	 */
	public static function get_order_items_context_products( $order ) {
		$order_items = [];
		foreach ( $order as $order_id ) {
			$order_items[] = self::get_product_context( $order_id );
		}

		$product = [];
		foreach ( $order_items[0] as $item_key => $item_value ) {
			$product[ $item_key ] = implode(
				', ',
				array_map(
					function ( $entry ) use ( $item_key ) {
						$ent = $entry[ $item_key ];

						return $ent;
					},
					$order_items
				)
			);
		}

		return $product;
	}


	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WooCommerce' );
	}
}

IntegrationsController::register( WooCommerce::class );
