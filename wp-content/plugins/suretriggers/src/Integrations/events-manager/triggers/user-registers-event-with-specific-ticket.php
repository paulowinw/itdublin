<?php
/**
 * UserRegistersEventWithSpecificTicket.
 * php version 5.6
 *
 * @category UserRegistersEventWithSpecificTicket
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EventsManager\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserRegistersEventWithSpecificTicket' ) ) :

	/**
	 * PurchaseMembership
	 *
	 * @category PurchaseMembership
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserRegistersEventWithSpecificTicket {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'EventsManager';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'em_user_registers_event_with_specific_ticket';

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
				'label'         => __( 'User Registers Event with Specific Ticket', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'em_bookings_added',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 *
		 * @param object $em_booking_obj Event data.
		 *
		 * @return void
		 */
		public function trigger_listener( $em_booking_obj ) {

			if ( ! is_object( $em_booking_obj ) ) {
				return;
			}
			global $wpdb;

			if ( property_exists( $em_booking_obj, 'event_id' ) ) {
				$event_id = $em_booking_obj->event_id;

				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id ) );
			}

			if ( ! empty( $all_bookings ) && property_exists( $em_booking_obj, 'person_id' ) ) {
				$user_id  = $em_booking_obj->person_id;
				$location = $wpdb->get_row(
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}em_locations as b 
					WHERE b.location_id  = %s",
						$all_bookings->location_id 
					) 
				);

				$all_ticket_bookings = $wpdb->get_row(
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}em_tickets_bookings as b 
					INNER JOIN {$wpdb->prefix}em_tickets as e 
					ON b.ticket_id = e.ticket_id WHERE b.booking_id = %d 
					ORDER BY b.ticket_booking_id DESC LIMIT 1",
						$all_bookings->booking_id 
					) 
				);

				$bookings_str        = wp_json_encode( $all_bookings );
				$ticket_bookings_str = wp_json_encode( $all_ticket_bookings );

				$context = array_merge(
					WordPress::get_user_context( $user_id )
				);
				if ( is_string( $bookings_str ) && is_string( $ticket_bookings_str ) ) {
					$bookings_arr        = json_decode( $bookings_str, true );
					$ticket_bookings_arr = json_decode( $ticket_bookings_str, true );
					if ( is_array( $bookings_arr ) && is_array( $ticket_bookings_arr ) ) {
						$context = array_merge(
							$context,
							$bookings_arr,
							$ticket_bookings_arr
						);
					}
				}
				if ( ! empty( $location ) ) {
					$context = array_merge( $context, (array) $location );
				}
				$context['post_id']   = $all_bookings->post_id;
				$context['ticket_id'] = $all_ticket_bookings->ticket_id;
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}

	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	UserRegistersEventWithSpecificTicket::get_instance();

endif;
