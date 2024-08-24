<?php
/**
 * NewOrderPlaced.
 * php version 5.6
 *
 * @category NewOrderPlaced
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'NewOrderPlaced' ) ) :

	/**
	 * NewOrderPlaced
	 *
	 * @category NewOrderPlaced
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class NewOrderPlaced {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Voxel';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'voxel_new_order_placed';

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
				'label'         => __( 'New Order Placed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'voxel/app-events/products/orders/customer:order_placed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $event Event.
		 * @return void
		 */
		public function trigger_listener( $event ) {
			if ( ! property_exists( $event, 'order' ) || ! property_exists( $event, 'customer' ) ) {
				return;
			}
			// Get Order.
			$order                      = $event->order;
			$context['id']              = $order->get_id();
			$context['vendor_id']       = $order->get_vendor_id();
			$context['details']         = $order->get_details();
			$context['payment_method']  = $order->get_payment_method_key();
			$context['tax_amount']      = $order->get_tax_amount();
			$context['discount_amount'] = $order->get_discount_amount();
			$context['shipping_amount'] = $order->get_shipping_amount();
			$context['status']          = $order->get_status();
			$context['created_at']      = $order->get_created_at();
			$context['subtotal']        = $order->get_subtotal();
			$context['total']           = $order->get_total();

			// Get order items.
			$order_items                 = $order->get_items();
			$context['order_item_count'] = $order->get_item_count();
			foreach ( $order_items as $item ) {
				$addon_data = [];
				if ( is_object( $item ) && method_exists( $item, 'get_addons' ) ) {
					$addons = $item->get_addons();
					if ( $addons && isset( $addons['summary'] ) ) {
						$addon_data = $addons['summary'];
					}
				}
				$context['order_items'][] = [
					'id'                    => $item->get_id(),
					'type'                  => $item->get_type(),
					'currency'              => $item->get_currency(),
					'quantity'              => $item->get_quantity(),
					'subtotal'              => $item->get_subtotal(),
					'product_id'            => $item->get_post()->get_id(),
					'product_label'         => $item->get_product_label(),
					'product_thumbnail_url' => $item->get_product_thumbnail_url(),
					'product_link'          => $item->get_product_link(),
					'description'           => $item->get_product_description(),
					'addon_data'            => $addon_data,
				];
				// If booking item, get booking details.
				if ( 'booking' === $item->get_type() ) {
					$details                                = $item->get_order_page_details();
					$context['order_items']['booking_type'] = $details['booking']['type'];
					if ( isset( $details['booking']['count_mode'] ) ) {
						$context['order_items']['booking_count_mode'] = $details['booking']['count_mode'];
					}
					if ( 'date_range' === $details['booking']['type'] ) {
						$context['order_items']['booking_start_date'] = $details['booking']['start_date'];
						$context['order_items']['booking_end_date']   = $details['booking']['end_date'];
					} elseif ( 'single_day' === $details['booking']['type'] ) {
						$context['order_items']['booking_date'] = $details['booking']['date'];
					} elseif ( 'timeslots' === $details['booking']['type'] ) {
						$context['order_items']['booking_date']      = $details['booking']['date'];
						$context['order_items']['booking_slot_from'] = $details['booking']['slot']['from'];
						$context['order_items']['booking_slot_to']   = $details['booking']['slot']['to'];                           
					}
				}
			}

			// Get Customer.
			$context['customer'] = WordPress::get_user_context( $event->customer->get_id() );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	NewOrderPlaced::get_instance();

endif;
