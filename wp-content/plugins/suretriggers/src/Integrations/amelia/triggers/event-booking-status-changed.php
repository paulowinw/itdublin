<?php
/**
 * EventBookingStatusChanged.
 * php version 5.6
 *
 * @category EventBookingStatusChanged
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Amelia\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'EventBookingStatusChanged' ) ) :

	/**
	 * EventBookingStatusChanged
	 *
	 * @category EventBookingStatusChanged
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class EventBookingStatusChanged {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Amelia';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'amelia_event_booking_status_changed';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since 1.0.0
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
				'label'         => __( 'Event Booking Status Changed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'AmeliaEventBookingStatusUpdated',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $args Appointment Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $args ) {
			if ( empty( $args ) ) {
				return;
			}

			if ( 'event' !== $args['type'] ) {
				return;
			}

			global $wpdb;

			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * 
					FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
					INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
					ON customer.id = event_period.customerBookingId 
					WHERE event_period.customerBookingId = ( Select max(id) From ' . $wpdb->prefix . 'amelia_customer_bookings ) AND event_period.eventPeriodId = %d',
					[ $args['id'] ]
				),
				ARRAY_A
			);

			$event      = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events WHERE id = %d',
					[ $args['id'] ]
				),
				ARRAY_A
			);
			$event_tags = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events_tags WHERE eventId = %d',
					[ $args['id'] ]
				),
				ARRAY_A
			);
			$tags       = [];
			if ( ! empty( $event_tags ) ) {
				foreach ( $event_tags as $key => $tag ) {
					$tags['event_tag'][ $key ] = $tag['name'];
				}
			} else {
				$tags = [];
			}
			end( $args['bookings'] );
			$customer_id     = key( $args['bookings'] );
			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $args['bookings'][ $customer_id ]['customerId'] ]
				),
				ARRAY_A
			);

			if ( $args['bookings'][ $customer_id ]['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $args['bookings'][ $customer_id ]['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context                         = array_merge( $result, $fields_arr, $event, $customer_result, $coupon_result, $tags );
			$context['amelia_events_list']   = $args['id'];
			$context['event_booking_status'] = $args['status'];

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
	EventBookingStatusChanged::get_instance();

endif;
