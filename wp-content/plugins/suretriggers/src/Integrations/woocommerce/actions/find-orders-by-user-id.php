<?php
/**
 * FindOrdersByUserID.
 * php version 5.6
 *
 * @category FindOrdersByUserID
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WooCommerce\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Traits\SingletonLoader;

/**
 * FindOrdersByUserID
 *
 * @category FindOrdersByUserID
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FindOrdersByUserID extends AutomateAction {

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
	public $action = 'wc_find_orders_by_user_id';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Find Orders by User ID', 'suretriggers' ),
			'action'   => 'wc_find_orders_by_user_id',
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
		
		$order_arg       = [
			'customer_id' => $user_id,
			'limit'       => -1,
		];
		$customer_orders = wc_get_orders( $order_arg );
		if ( empty( $customer_orders ) ) {
			throw new Exception( 'There are no orders for this user.' );
		}
		$ids           = [];
		$status        = [];
		$product_ids   = [];
		$product_names = [];
		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		foreach ( $customer_orders as $customer_order ) {
			$order = wc_get_order( $customer_order );
			if ( $order ) {
				if ( $order->has_status( [ 'completed' ] ) ) {
					$ids[]    = $order->get_id();
					$status[] = $order->get_status();
					$items    = $order->get_items();
					foreach ( $items as $item ) {
						$product         = $item->get_product();
						$product_ids[]   = $product->get_id();
						$product_names[] = $product->get_name();
					}
				}
			}
		}
		if ( empty( $ids ) && empty( $product_ids ) ) {
			throw new Exception( 'There are no completed orders for this user.' );
		}
		$context = [
			'ids'           => implode( ', ', $ids ),
			'status'        => implode( ', ', $status ),
			'product_ids'   => implode( ', ', $product_ids ),
			'product_names' => implode( ', ', $product_names ),
		];
		return $context;
	}
}

FindOrdersByUserID::get_instance();
