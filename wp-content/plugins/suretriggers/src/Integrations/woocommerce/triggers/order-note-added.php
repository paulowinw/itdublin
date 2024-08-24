<?php
/**
 * OrderNoteAdded.
 * php version 5.6
 *
 * @category OrderNoteAdded
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WooCommerce\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Traits\SingletonLoader;

/**
 * OrderNoteAdded
 *
 * @category OrderNoteAdded
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class OrderNoteAdded {

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
	public $trigger = 'wc_order_note_added';

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
			'event_name'    => 'woocommerce_order_note_added',
			'label'         => __( 'Order Note Added', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'woocommerce_order_note_added',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 15,
			'accepted_args' => 2,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int|false $comment_id Comment ID.
	 * @param object    $order Order.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $comment_id, $order ) {

		if ( ! $order ) {
			return;
		}

		global $wpdb;
		if ( is_object( $order ) && method_exists( $order, 'get_items' ) ) {
			$items       = $order->get_items();
			$product_ids = [];
			foreach ( $items as $item ) {
				$product_ids[] = $item['product_id'];
			}
			
			$product_data = [];
			foreach ( $product_ids as $key => $product_id ) {
				$product_data[ 'product' . $key ] = WooCommerce::get_product_context( $product_id );
				$terms                            = get_the_terms( $product_id, 'product_cat' );
				if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
					$cat_name = [];
					foreach ( $terms as $cat ) {
						$cat_name[] = $cat->name;
					}
					$product_data[ 'product' . $key ]['category'] = implode( ', ', $cat_name );
				}
				$terms_tags = get_the_terms( $product_id, 'product_tag' );
				if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
					$tag_name = [];
					foreach ( $terms_tags as $tag ) {
						$tag_name[] = $tag->name;
					}
					$product_data[ 'product' . $key ]['tag'] = implode( ', ', $tag_name );
				}
			}
			if ( is_object( $order ) && method_exists( $order, 'get_id' ) ) {
				$order_id = $order->get_id();
				$context  = WooCommerce::get_order_context( $order_id );
				$results  = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT *
						FROM {$wpdb->prefix}comments
						WHERE  `comment_post_ID` = %d
						AND  `comment_type` LIKE %s
					",
						$order_id,
						'order_note'
					)
				);
		
				$comment_meta = get_comment_meta( (int) $comment_id, 'is_customer_note', true );
		
				if ( '' != $comment_meta ) {
					$context['note_type'] = 'customer';
				} else {
					$context['note_type'] = 'internal';
				}
		
				foreach ( $results as $note ) {
					$context['note'] = [
						'id'      => $note->comment_ID,
						'date'    => $note->comment_date,
						'author'  => $note->comment_author,
						'content' => $note->comment_content,
					];
				}
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}
}

OrderNoteAdded::get_instance();
