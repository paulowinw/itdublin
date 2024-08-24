<?php
/**
 * AddSubscriptionCoupon.
 * php version 5.6
 *
 * @category AddSubscriptionCoupon
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceSubscriptions\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * AddSubscriptionCoupon
 *
 * @category AddSubscriptionCoupon
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddSubscriptionCoupon extends AutomateAction {

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
	public $action = 'wc_add_subscription_coupon';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Coupon', 'suretriggers' ),
			'action'   => 'wc_add_subscription_coupon',
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
	 * @return object|array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$subscription_id = $selected_options['subscription_id'];
		$coupon          = $selected_options['coupon_code'];

		if ( function_exists( 'wcs_get_subscription' ) ) {
			$subscription = wcs_get_subscription( $subscription_id );
			if ( $subscription ) {
				$response = $subscription->apply_coupon( $coupon );

				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message() );
				}
				$user_id = $subscription->get_user_id();
				$items   = $subscription->get_items();
				if ( ! empty( $items ) ) {
					$product_ids = [];
					foreach ( $items as $item ) {
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_ids[] = $item->get_product_id();
						}
					}
					if ( is_object( $subscription ) ) {
						$subscription = get_object_vars( $subscription );
					}
				}
				$context         = $subscription;
				$context['user'] = WordPress::get_user_context( $user_id );
				if ( ! empty( $product_ids ) ) {
					foreach ( $product_ids as $val ) {
						$context['subscription']      = $val;
						$context['subscription_name'] = get_the_title( $val );
					}
				}
				
				return $context;
			} else {
				throw new Exception( 'Subscription not found for the provided Subscription ID.' );
			}
		}
	}
}

AddSubscriptionCoupon::get_instance();
