<?php
/**
 * AttendeeRegisteredEvent.
 * php version 5.6
 *
 * @category AttendeeRegisteredEvent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EventCalendar\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'AttendeeRegisteredEvent' ) ) :

	/**
	 * AttendeeRegisteredEvent
	 *
	 * @category AttendeeRegisteredEvent
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AttendeeRegisteredEvent {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'TheEventCalendar';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'attendee_registered_event';

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
				'label'         => __( 'Attendee Registered for Event', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [
					'event_tickets_rsvp_attendee_created',
					'event_ticket_woo_attendee_created',
					'event_ticket_edd_attendee_created',
					'event_tickets_tpp_attendee_created',
					'event_tickets_tpp_attendee_updated',
					'tec_tickets_commerce_attendee_after_create',
				],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 5,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $attendee_id Attendee ID.
		 * @param int    $post_id Post ID.
		 * @param object $order Order.
		 * @param int    $attendee_product_id Attendee Product ID.
		 * @param string $attendee_order_status Attendee Order Status.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $attendee_id, $post_id, $order, $attendee_product_id, $attendee_order_status = null ) {

			if ( is_object( $attendee_id ) && 'tec_tickets_commerce_attendee_after_create' === (string) current_action() ) {
				$post_id     = $attendee_id->event_id;
				$attendee_id = $attendee_id->ID;
			}
			if ( ! $attendee_id ) {
				return;
			}

			$attendees = tribe_tickets_get_attendees( $attendee_id );

			if ( empty( $attendees ) ) {
				return;
			}
			// Fetch unique values + all attendee details.
			$attendee_details = [];
			foreach ( $attendees as $attendee ) {
				foreach ( $attendee as $key => $value ) {
					if ( ! isset( $attendee_details[ $key ] ) ) {
						$attendee_details[ $key ] = $value;
					} else {
						if ( $attendee_details[ $key ] !== $value ) {
							if ( ! is_array( $attendee_details[ $key ] ) ) {
								$attendee_details[ $key ] = [ $attendee_details[ $key ] ];
							}
							if ( ! in_array( $value, $attendee_details[ $key ] ) ) {
								$attendee_details[ $key ][] = $value;
							}
						}
					}
				}
			}

			if ( 'tec_tickets_commerce_attendee_after_create' === (string) current_action() ) {
				$attendee_product_id = $attendee_details['product_id'];
			}

			$event   = tribe_events_get_ticket_event( $attendee_product_id );
			$context = [
				'event_id'  => $post_id,
				'event'     => $event,
				'attendies' => $attendee_details,
			];

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
	AttendeeRegisteredEvent::get_instance();

endif;
