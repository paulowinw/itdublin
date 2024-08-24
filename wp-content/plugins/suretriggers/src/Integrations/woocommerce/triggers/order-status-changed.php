<?php
/**
 * OrderStatusChanged.
 * php version 5.6
 *
 * @category OrderStatusChanged
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WooCommerce\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * OrderStatusChanged
 *
 * @category OrderStatusChanged
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class OrderStatusChanged {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WooCommerce';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wc_order_status_changed';

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 * @return array
	 */
	public function register( $triggers ) {
		$triggers[ $this->integration ][ $this->trigger ] = [
			'event_name'    => 'woocommerce_order_status_changed',
			'label'         => __( 'Order Status Changes', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'woocommerce_order_status_changed',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 4,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int    $order_id order ID.
	 * @param string $from_status order old status.
	 * @param string $to_status  order new status.
	 * @param array  $order  order.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $order_id, $from_status, $to_status, $order ) {
		if ( ! $order_id ) {
			return;
		}
		$order = wc_get_order( $order_id );
		
		if ( ! $order ) {
			return;
		}

		if ( is_object( $order ) && method_exists( $order, 'get_items' ) ) {
			$items       = $order->get_items();
			$product_ids = [];
			foreach ( $items as $item ) {
				$product_ids[] = $item['product_id'];
			}
			$product_data = [];
			foreach ( $product_ids as $key => $product_id ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$product_data[ 'product' . $key ] = WooCommerce::get_product_context( $product_id );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$terms = get_the_terms( $product_id, 'product_cat' );
				if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
					$cat_name = [];
					foreach ( $terms as $cat ) {
						$cat_name[] = $cat->name;
					}
					$product_data[ 'product' . $key ]['category'] = implode( ', ', $cat_name );
				}
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$terms_tags = get_the_terms( $product_id, 'product_tag' );
				if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
					$tag_name = [];
					foreach ( $terms_tags as $tag ) {
						$tag_name[] = $tag->name;
					}
					$product_data[ 'product' . $key ]['tag'] = implode( ', ', $tag_name );
				}
			}
			$order_detail = WooCommerce::get_order_context( $order_id );
			if ( is_array( $order_detail ) ) {
				$context = array_merge(
					$order_detail,
					$product_data
				);
			}
	
			if ( is_object( $order ) && method_exists( $order, 'get_customer_id' ) ) {
				$user_id         = $order->get_customer_id();
				$context['user'] = WordPress::get_user_context( $user_id );
			}
	
			$context['to_status']   = 'wc-' . $to_status;
			$context['from_status'] = 'wc-' . $from_status;
	
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}
}

OrderStatusChanged::get_instance();
