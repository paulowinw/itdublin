<?php
/**
 * AddOrderNote.
 * php version 5.6
 *
 * @category AddOrderNote
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Woocommerce\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Traits\SingletonLoader;
use WC_Order;

/**
 * AddOrderNote
 *
 * @category AddOrderNote
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddOrderNote extends AutomateAction {

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
	public $action = 'wc_add_order_note';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Order Note', 'suretriggers' ),
			'action'   => 'wc_add_order_note',
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
		$order_id  = $selected_options['order_id'];
		$note_type = $selected_options['note_type'];
		$note_text = $selected_options['note_text'];

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			throw new Exception( 'No order found with the specified Order ID.' );
		}

		if ( method_exists( $order, 'add_order_note' ) ) {
			$is_customer = (int) ( 'customer' === $note_type );
			$order->add_order_note( $note_text, $is_customer, false );
			global $wpdb;
			$results = $wpdb->get_results(
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

			foreach ( $results as $note ) {
				$context['note'] = [
					'id'      => $note->comment_ID,
					'date'    => $note->comment_date,
					'author'  => $note->comment_author,
					'content' => $note->comment_content,
				];
				$comment_meta    = get_comment_meta( $note->comment_ID, 'is_customer_note', true );

				if ( '' != $comment_meta ) {
					$context['note_type'] = 'customer';
				} else {
					$context['note_type'] = 'internal';
				}
			}
			if ( ! empty( $context ) && is_array( $context ) && is_array( WooCommerce::get_order_context( $order_id ) ) ) {
				return array_merge( WooCommerce::get_order_context( $order_id ), $context );
			}
		}
	}
}

AddOrderNote::get_instance();
