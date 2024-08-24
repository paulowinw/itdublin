<?php
/**
 * FindSubscriptionByUserID.
 * php version 5.6
 *
 * @category FindSubscriptionByUserID
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
use WC_Subscription;

/**
 * FindSubscriptionByUserID
 *
 * @category FindSubscriptionByUserID
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FindSubscriptionByUserID extends AutomateAction {

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
	public $action = 'wc_find_subscription_by_user_id';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Find Subscription by User ID', 'suretriggers' ),
			'action'   => 'wc_find_subscription_by_user_id',
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
		$user_id = $selected_options['user_id'];
		
		if ( function_exists( 'wcs_get_users_subscriptions' ) ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$users_subscriptions = wcs_get_users_subscriptions( $user_id );
				
				if ( ! empty( $users_subscriptions ) ) {
					$ids           = [];
					$status        = [];
					$product_ids   = [];
					$product_names = [];
					foreach ( $users_subscriptions as $key => $subscription ) {
						if ( $subscription->has_status( [ 'active', 'pending-cancel' ] ) ) {
							
							$ids[]    = $key;
							$status[] = $subscription->get_status();
							$items    = $subscription->get_items();
							if ( ! empty( $items ) ) {
								foreach ( $items as $item ) {
									$product = $item->get_product();
									if ( $product->is_type( [ 'variable-subscription', 'subscription_variation' ] ) ) {
										$product_ids[]   = $item->get_variation_id();
										$product_names[] = get_the_title( $item->get_variation_id() );
									} else {
										$product_ids[]   = $item->get_product_id();
										$product_names[] = get_the_title( $item->get_product_id() );
									}
								}
							}
						}
					}
					if ( empty( $ids ) && empty( $product_ids ) ) {
						throw new Exception( 'There are no active subscriptions for this user.' );
					}
					$context = [
						'ids'           => implode( ', ', $ids ),
						'status'        => implode( ', ', $status ),
						'product_ids'   => implode( ', ', $product_ids ),
						'product_names' => implode( ', ', $product_names ),
					];
				} else {
					throw new Exception( 'There are no subscriptions for this user.' );
				}
				return $context;
			} else {
				throw new Exception( 'User does not exists for the provided User ID.' );
			}
		}
	}
}

FindSubscriptionByUserID::get_instance();
