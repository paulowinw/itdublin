<?php
/**
 * UpdateSubscriptionEndDate.
 * php version 5.6
 *
 * @category UpdateSubscriptionEndDate
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
 * UpdateSubscriptionEndDate
 *
 * @category UpdateSubscriptionEndDate
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdateSubscriptionEndDate extends AutomateAction {

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
	public $action = 'wc_update_subscription_end_date';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Update Subscription End Date', 'suretriggers' ),
			'action'   => 'wc_update_subscription_end_date',
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
		$date            = $selected_options['end_date'];

		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return;
		}
		$subscription = wcs_get_subscription( $subscription_id );
		if ( $subscription ) {
			$user_id = $subscription->get_user_id();
			if ( strtotime( $date ) == false ) {
				throw new Exception( 'Provided End Payment Date is not valid.' );
			}
			$datetime     = strtotime( $date );
			$dates['end'] = gmdate( 'Y-m-d H:i:s', $datetime );

			try {
				$subscription->update_dates( $dates );
				wp_cache_delete( $subscription_id, 'posts' );
				$items       = $subscription->get_items();
				$product_ids = [];
				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$product = $item->get_product();
						if ( class_exists( '\WC_Subscriptions_Product' ) && \WC_Subscriptions_Product::is_subscription( $product ) ) {
							$product_ids[] = $item->get_product_id();
						}
					}
				}
				
				$subscription_status            = $subscription->get_status();
				$subscription_start_date        = $subscription->get_date_created();
				$subscription_next_payment_date = $subscription->get_date( 'next_payment' );
				
				$context['subscription'] = [
					'status'            => $subscription_status,
					'start_date'        => $subscription_start_date,
					'next_payment_date' => $subscription_next_payment_date,
					'end_date'          => $subscription->get_date( 'end' ),
				];
				if ( ! empty( $product_ids ) ) {
					foreach ( $product_ids as $val ) {
						$context['id']   = $val;
						$context['name'] = get_the_title( $val );
					}
				}
				return array_merge( $context, WordPress::get_user_context( $user_id ) );
			} catch ( Exception $e ) {
				throw new Exception( $e->getMessage() );
			}
		} else {
			throw new Exception( 'Subscription not found for the provided Subscription ID.' );
		}
	}
}

UpdateSubscriptionEndDate::get_instance();
