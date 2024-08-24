<?php
/**
 * Create Coupon.
 * php version 5.6
 *
 * @category CreateCoupon
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPress\Actions;

use Exception;
use MeprEvent;
use MeprProduct;
use MeprTransaction;
use MeprUser;
use MeprUtils;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * CreateCoupon
 *
 * @category CreateCoupon
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateCoupon extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MemberPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mp_create_coupon';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Coupon', 'suretriggers' ),
			'action'   => $this->action,
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
	 * @throws Exception Throws exception.
	 *
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$coupon_code = ( isset( $selected_options['coupon_code'] ) ) ? $selected_options['coupon_code'] : '';
		$new_post    = [
			'post_title'  => $coupon_code,
			'post_name'   => strtolower( $coupon_code ),
			'post_status' => 'publish',
			'post_author' => $user_id,
			'post_type'   => 'memberpresscoupon',
		];
		$couponexist = get_page_by_title( $coupon_code, OBJECT, 'memberpresscoupon' ); // @phpcs:ignore
		if ( $couponexist ) {
			throw new Exception( 'Coupon is already exist with provided code' );
		}
		// Insert the post into the database.
		$new_coupon = wp_insert_post( $new_post );
		if ( isset( $selected_options['allow_upgrade_downgrades'] ) ) {
			$allow_upgrade = $selected_options['allow_upgrade_downgrades'];
			update_post_meta( $new_coupon, '_mepr_coupons_use_on_upgrades', $allow_upgrade );
		}
		if ( isset( $selected_options['start_date'] ) && '' !== $selected_options['start_date'] ) {
			$start_date = $selected_options['start_date'];
			$start_date = strtotime( $start_date );
			update_post_meta( $new_coupon, '_mepr_coupons_should_start', 1 );
			update_post_meta( $new_coupon, '_mepr_coupons_starts_on', $start_date );
			
		}

		if ( isset( $selected_options['expiration_date'] ) && '' !== $selected_options['expiration_date'] ) {
			$expiration_date = $selected_options['expiration_date'];
			$expiration_date = strtotime( $expiration_date );
			update_post_meta( $new_coupon, '_mepr_coupons_should_expire', 1 );
			update_post_meta( $new_coupon, '_mepr_coupons_expires_on', $expiration_date );
		}
		if ( isset( $selected_options['usage_count'] ) && '' !== $selected_options['usage_count'] ) {
			$usage_count = $selected_options['usage_count'];       
			update_post_meta( $new_coupon, '_mepr_coupons_usage_amount', $usage_count );
		}
		if ( isset( $selected_options['discount_amount'] ) && '' !== $selected_options['discount_amount'] ) {
			$discount_amount = $selected_options['discount_amount'];       
			update_post_meta( $new_coupon, '_mepr_coupons_discount_amount', $discount_amount );
		}
		
		if ( isset( $selected_options['discount_type'] ) && '' !== $selected_options['discount_type'] ) {
			$discount_type = $selected_options['discount_type'];       
			update_post_meta( $new_coupon, '_mepr_coupons_discount_type', $discount_type );
		}
		
		if ( isset( $selected_options['memberpressproduct'] ) && ! empty( $selected_options['memberpressproduct'] ) ) {
			$products = [];
			foreach ( $selected_options['memberpressproduct'] as $membership ) {
				array_push( $products, $membership['value'] );
			}
			update_post_meta( $new_coupon, '_mepr_coupons_valid_products', $products );
		} 
		update_post_meta( $new_coupon, '_mepr_coupons_discount_mode', 'standard' );
		
		$post      = get_post( $new_coupon );
		$post_meta = get_post_meta( $new_coupon );
		$context   = array_merge( (array) $post, (array) $post_meta );
		return $context;
	}
}

CreateCoupon::get_instance();
