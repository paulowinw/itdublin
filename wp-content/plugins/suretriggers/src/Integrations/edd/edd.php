<?php
/**
 * EDD core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\EDD;

use Easy_Digital_Downloads;
use EDD_Payment;
use EDD_Customer;
use EDD_SL_Download;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\EDD
 */
class EDD extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'EDD';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'EDD', 'suretriggers' );
		$this->description = __( 'Easy Digital Downloads is a complete eCommerce solution for selling digital products on WordPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/edd.svg';

		parent::__construct();
	}

	/**
	 * Get context for Order Created trigger.
	 *
	 * @param  EDD_Payment|object|null $payment payment.
	 * @param  string|null             $term trigger type.
	 * @param  integer|null            $download_id download id.
	 * @return array
	 */
	public static function get_product_purchase_context( EDD_Payment $payment, $term = null, $download_id = null ) {
		global $wpdb;
	
		$purchased_products     = implode(
			', ',
			array_map(
				function ( $entry ) {
					return $entry['name'];
				},
				$payment->cart_details
			)
		);
		$purchased_products_ids = implode(
			', ',
			array_map(
				function ( $entry ) {
					return $entry['id'];
				},
				$payment->cart_details
			)
		);

		$price_id        = static::get_item_price_id( $payment->cart_details[0] );
		$licenses_table  = $wpdb->prefix . 'edd_licenses';
		$licenses_result = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $licenses_table ) );
		if ( $licenses_result == $licenses_table ) {
			$licesnses = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_licenses WHERE payment_id= %s", $payment->ID ) );
		}
		$context                        = [];
		$context['order_id']            = $payment->ID;
		$context['customer_email']      = $payment->email;
		$context['customer_id']         = $payment->customer_id;
		$context['user_id']             = $payment->user_info['id'];
		$context['customer_first_name'] = $payment->first_name;
		$context['customer_last_name']  = $payment->last_name;
		$context['ordered_items']       = $purchased_products;
		$context['currency']            = $payment->currency;
		$context['status']              = $payment->status;
		$context['discount_codes']      = ( property_exists( $payment, 'discounts' ) ) ? $payment->discounts : 'NA';
		$context['order_discounts']     = number_format( $payment->order->discount, 2 );
		$context['order_subtotal']      = number_format( $payment->subtotal, 2 );
		$context['order_tax']           = number_format( $payment->tax, 2 );
		$context['order_total']         = number_format( $payment->total, 2 );
		$context['payment_method']      = $payment->gateway;
		$context['purchase_key']        = $payment->key;
		$context['ordered_items_ids']   = $purchased_products_ids;
		$context['customer_address']    = $payment->user_info['address'];
		if ( 'order_one_product' === $term ) {
			if ( $download_id > 0 ) {
				$context['download_id'] = $download_id;
			} else {
				$download_id            = $payment->cart_details[0]['id'];
				$context['download_id'] = $download_id;
			}
		}
		if ( ! empty( $price_id ) ) {
			$context['price_id'] = $price_id;
		}
		if ( ! empty( $licesnses ) ) {
			$context['license_key']             = $licesnses->license_key;
			$context['license_key_expire_date'] = $licesnses->expiration;
			$context['license_key_status']      = $licesnses->status;
		}

		// Fetch custom checkout fields.
		$post_id_option = get_option( 'cfm-checkout-form' );
		if ( is_numeric( $post_id_option ) ) {
			$post_id = (int) $post_id_option;
			$fields  = get_post_meta( $post_id, 'cfm-form', true );
			if ( is_array( $fields ) && ! empty( $fields ) && function_exists( 'edd_get_order_meta' ) ) {
				foreach ( $fields as $field ) {
					$context[ $field['name'] ] = edd_get_order_meta( $payment->ID, $field['name'], true );
				}
			}
		}
		return $context;
	}

	/**
	 * Get context for Stripe Payment Refunded trigger.
	 *
	 * @param EDD_Payment|object|null $order_detail order details.
	 * @return array
	 */
	public static function get_purchase_refund_context( EDD_Payment $order_detail ) {
		$total_discount      = 0;
		$item_names          = [];
		$download_product    = [];
		$download_product_id = [];

		$order_items = edd_get_payment_meta_cart_details( $order_detail->ID );

		foreach ( $order_items as $item ) {
			$item_names[]          = $item['name'];
			$download_product[]    = get_the_title( $item['id'] );
			$download_product_id[] = $item['id'];
			// Sum the discount.
			if ( is_numeric( $item['discount'] ) ) {
				$total_discount += $item['discount'];
			}
		}

		$context                          = [];
		$context['order_id']              = $order_detail->ID;
		$context['customer_id']           = $order_detail->customer_id;
		$context['user_id']               = $order_detail->user_id;
		$context['customer_email']        = $order_detail->email;
		$context['customer_first_name']   = $order_detail->first_name;
		$context['customer_last_name']    = $order_detail->last_name;
		$context['ordered_items']         = implode( ',', $item_names );
		$context['download_product_name'] = implode( ',', $download_product );
		$context['download_product_id']   = implode( ',', $download_product_id );
		$context['currency']              = $order_detail->currency;
		$context['status']                = $order_detail->status;
		$context['discount_codes']        = ( property_exists( $order_detail, 'discounts' ) ) ? $order_detail->discounts : 'NA';
		$context['order_discounts']       = number_format( $total_discount, 2 );
		$context['order_subtotal']        = number_format( $order_detail->subtotal, 2 );
		$context['order_tax']             = number_format( $order_detail->tax, 2 );
		$context['order_total']           = number_format( $order_detail->total, 2 );
		$context['payment_method']        = $order_detail->gateway;

		return $context;
	}

	/**
	 * Get relevant data for a given license ID.
	 *
	 * @since  1.1
	 *
	 * @param  integer $license_id License ID.
	 * @param  integer $download_id Downoad ID.
	 * @param  integer $payment_id Payment ID.
	 * @return array|void               License data.
	 */
	public static function edd_get_license_data( $license_id = 0, $download_id = 0, $payment_id = 0 ) {
		
		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return;
		}
		$license = edd_software_licensing()->get_license( $license_id );
		// The license ID supplied didn't give us a valid license, no data to return.
		if ( false === $license ) {
			return [];
		}

		if ( empty( $download_id ) ) {

			$download_id = $license->download_id;

		}

		if ( empty( $payment_id ) ) {

			$payment_id = $license->payment_id;

		}
		if ( ! function_exists( 'edd_get_payment_customer_id' ) ) {
			return;
		}
		if ( ! class_exists( 'EDD_Customer' ) ) {
			return;
		}
		$customer_id = edd_get_payment_customer_id( $payment_id );
		$price_id    = $license->price_id;
		if ( empty( $customer_id ) ) {
			if ( ! function_exists( 'edd_get_payment_meta_user_info' ) || ! function_exists( 'edd_get_payment_user_email' ) ) {
				return;
			}
			$user_info       = edd_get_payment_meta_user_info( $payment_id );
			$customer        = new EDD_Customer();
			$customer->email = edd_get_payment_user_email( $payment_id );
			$customer->name  = $user_info['first_name'];

		} else {
		
			$customer = new EDD_Customer( $customer_id );

		}
		$expiration = null;
		if ( $license->is_lifetime ) {
			$expiration = 'never';
		} elseif ( $license->expiration && is_numeric( $license->expiration ) ) {
			$expiration = $license->expiration;
		}
		if ( ! class_exists( 'EDD_SL_Download' ) ) {
			return;
		}
		$download     = new EDD_SL_Download( $download_id );
		$license_data = [
			'ID'               => $license->ID,
			'key'              => $license->key,
			'customer_email'   => $customer->email,
			'customer_name'    => $customer->name,
			'customer_id'      => $customer->id,
			'user_id'          => $customer->user_id,
			'download_id'      => $download_id,
			'price_id'         => $price_id,
			'product_name'     => $download->get_name(),
			'activation_limit' => $license->activation_limit,
			'activation_count' => $license->activation_count,
			'activated_urls'   => implode( ',', $license->sites ),
			'expiration'       => $expiration,
			'is_lifetime'      => $license->is_lifetime ? '1' : '0',
			'status'           => $license->status,
		];

		return $license_data;
	}

	/**
	 * Get price id.
	 *
	 * @param  array $item cart item.
	 * @return array
	 */
	public static function get_item_price_id( $item = [] ) {
		if ( isset( $item['item_number'] ) ) {
			$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
		} else {
			$price_id = isset( $item['options']['price_id'] ) ? $item['options']['price_id'] : null;
		}
	
		return $price_id;
	}

	/**
	 * Is Plugin dependent plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( Easy_Digital_Downloads::class );
	}

	/**
	 * Get context for products actions.
	 *
	 * @param  array        $payments payments.
	 * @param  string|null  $term trigger type.
	 * @param  integer|null $download_id download id.
	 * @return array
	 */
	public static function get_all_product_purchase_context( $payments, $term = null, $download_id = null ) {
		$data = [];
		foreach ( $payments as $payment ) {
			$data[] = static::get_product_purchase_context( $payment, 'edd_action', $download_id );
		}
		return $data;
	}

}

IntegrationsController::register( EDD::class );
