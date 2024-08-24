<?php
/**
 * CreateCouponCode.
 * php version 5.6
 *
 * @category CreateCouponCode
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Woocommerce\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * CreateCouponCode
 *
 * @category CreateCouponCode
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateCouponCode extends AutomateAction {

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
	public $action = 'wc_create_coupon_code';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Generate a coupon code.', 'suretriggers' ),
			'action'   => 'wc_create_coupon_code',
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
	 * @return void|array|bool
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! $user_id ) {
			$this->set_error(
				[
					'msg' => __( 'User Not found', 'suretriggers' ),
				]
			);
			return false;
		}

		foreach ( $fields as $field ) {
			if ( array_key_exists( 'validationProps', $field ) && empty( $selected_options[ $field['name'] ] ) ) {
				$this->set_error(
					[
						'msg' => __( 'Required field is missing: ', 'suretriggers' ) . $field['name'],
					]
				);
				return false;
			}
		}

		$coupon_code              = ! empty( $selected_options['coupon_code'] ) ? $selected_options['coupon_code'] : $this->get_coupon_code();
		$description              = $selected_options['description'];
		$discount_type            = $selected_options['discount_type'];
		$coupon_amt               = $selected_options['coupon_amt'];
		$is_free_shipping         = 1 === $selected_options['is_free_shipping'] ? 'yes' : 'no';
		$expiry_date              = $selected_options['expiry_date'];
		$min_spend                = $selected_options['min_spend'];
		$max_spend                = $selected_options['max_spend'];
		$is_individual            = 1 === $selected_options['is_individual'] ? 'yes' : 'no';
		$exclude_sale_items       = 1 === $selected_options['exclude_sale_items'] ? 'yes' : 'no';
		$allowed_emails           = ! empty( $selected_options['allowed_emails'] ) ? array_filter( array_map( 'sanitize_email', explode( ',', $selected_options['allowed_emails'] ) ) ) : '';
		$usage_limit_per_coupon   = $selected_options['usage_limit_per_coupon'];
		$limit_items              = $selected_options['limit_items'];
		$usage_limit_per_user     = $selected_options['usage_limit_per_user'];
		$product_cat_ids          = [];
		$product_cat_name         = [];
		$product_cat_exclude_ids  = [];
		$product_cat_exclude_name = [];
		$product_ids              = [];
		$product_names            = [];
		$exclude_product_ids      = [];
		$exclude_product_names    = [];

		if ( isset( $selected_options['product_cat'] ) ) {
			foreach ( $selected_options['product_cat'] as $product_cat ) {
				$product_cat_ids[]  = $product_cat['value'];
				$product_cat_name[] = $product_cat['label'];
			}
		}
		if ( isset( $selected_options['exclude_product_cat'] ) ) {
			foreach ( $selected_options['exclude_product_cat'] as $exclude_product_cat ) {
				$product_cat_exclude_ids[]  = $exclude_product_cat['value'];
				$product_cat_exclude_name[] = $exclude_product_cat['label'];
			}
		}
		if ( isset( $selected_options['product'] ) ) {
			foreach ( $selected_options['product'] as $product ) {
				$product_ids[]   = $product['value'];
				$product_names[] = $product['label'];
			}
		}
		if ( isset( $selected_options['exclude_product'] ) ) {
			foreach ( $selected_options['exclude_product'] as $exclude_product ) {
				$exclude_product_ids[]   = $exclude_product['value'];
				$exclude_product_names[] = $exclude_product['label'];
			}
		}
		$products         = ! empty( $product_ids ) ? implode( ',', array_map( 'intval', $product_ids ) ) : '';
		$exclude_products = ! empty( $exclude_product_ids ) ? implode( ',', array_map( 'intval', $exclude_product_ids ) ) : '';
		$args             = [
			'post_title'   => $coupon_code,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_type'    => 'shop_coupon',
			'post_excerpt' => $description,
		];

		$coupon_id = wp_insert_post( $args );

		$coupon_data = [
			'discount_type'                        => $discount_type,
			'coupon_amount'                        => $coupon_amt,
			'is_free_shipping'                     => $is_free_shipping,
			'expiry_date'                          => $expiry_date,
			'minimum_amount'                       => $min_spend,
			'maximum_amount'                       => $max_spend,
			'is_individual'                        => $is_individual,
			'exclude_sale_items'                   => $exclude_sale_items,
			'product_ids'                          => $products,
			'product_ids_list'                     => implode( ',', $product_ids ),
			'product_names'                        => $product_names,
			'product_names_list'                   => implode( ',', $product_names ),
			'exclude_product_ids'                  => $exclude_products,
			'exclude_product_ids_list'             => implode( ',', $exclude_product_ids ),
			'exclude_product_names'                => $exclude_product_names,
			'exclude_product_names_list'           => implode( ',', $exclude_product_names ),
			'product_categories'                   => $product_cat_ids,
			'product_categories_list'              => implode( ',', $product_cat_ids ),
			'product_categories_name'              => $product_cat_name,
			'product_categories_name_list'         => implode( ',', $product_cat_name ),
			'exclude_product_categories'           => $product_cat_exclude_ids,
			'exclude_product_categories_list'      => implode( ',', $product_cat_exclude_ids ),
			'exclude_product_categories_name'      => $product_cat_exclude_name,
			'exclude_product_categories_name_list' => implode( ',', $product_cat_exclude_name ),
			'customer_email'                       => $allowed_emails,
			'usage_limit'                          => $usage_limit_per_coupon,
			'limit_usage_to_x_items'               => $limit_items,
			'usage_limit_per_user'                 => $usage_limit_per_user,
		];

		if ( $coupon_id ) {
			$coupon_meta_fields                 = $coupon_data;
			$coupon_meta_fields['date_expires'] = $expiry_date;

			foreach ( $coupon_meta_fields as $key => $value ) {
				update_post_meta( $coupon_id, $key, $value );
			}
		}

		$user_firstname = get_user_meta( $user_id, 'first_name', true );
		$coupon_code    = get_the_title( $coupon_id );

		$variable_fields                           = $coupon_data;
		$variable_fields['user_first_name']        = $user_firstname;
		$variable_fields['coupon_code']            = $coupon_code;
		$variable_fields['coupon_id']              = $coupon_id;
		$variable_fields['coupon_description']     = $description;
		$variable_fields['client_parse_site_name'] = get_bloginfo( 'name' );

		return $variable_fields;
	}

	/**
	 * Generate random coupon code.
	 *
	 * @return string|false
	 */
	public function get_coupon_code() {
		return substr( str_shuffle( 'ABCDEFGHJKMNPQRSTUVWXYZ23456789' ), 0, 8 );
	}
}

CreateCouponCode::get_instance();
