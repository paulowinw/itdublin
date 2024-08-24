<?php
/**
 * EventCalendarSendRsvp.
 * php version 5.6
 *
 * @category EventCalendarSendRsvp
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * EventCalendarSendRsvp
 *
 * @category EventCalendarSendRsvp
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class EventCalendarSendRsvp extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'TheEventCalendar';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'event_calendar_send_rsvp';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'RSVP on behalf of the user for an event', 'suretriggers' ),
			'action'   => $this->action,
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
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'Tribe__Tickets__Main' ) || ! class_exists( 'Tribe__Events__Main' ) ) {
			throw new Exception( 'The Events Calendar or Event Tickets plugin not installed.' );
		}

		$attendee_name    = $selected_options['attendee_name'];
		$attendee_email   = $selected_options['attendee_email'];
		$number_of_guests = $selected_options['number_of_guests'];

		if ( ! is_numeric( $number_of_guests ) ) {
			throw new Exception( 'Number of Guests should be a numeric value.' );
		}

		$ticket_handler   = new Tribe__Tickets__Tickets_Handler();
		$get_rsvp_tickets = $ticket_handler->get_event_rsvp_tickets( get_post( $selected_options['event_calendar_rsvp_event'] ) );

		if ( empty( $get_rsvp_tickets ) ) {
			return false;
		}

		$ticket_id = 0;
		foreach ( $get_rsvp_tickets as $rsvp_ticket ) {
			if ( $rsvp_ticket->capacity < 0 ) {
				$ticket_id = $rsvp_ticket->ID;
			} elseif ( $rsvp_ticket->capacity > 0 && $rsvp_ticket->capacity > $rsvp_ticket->qty_sold ) {
				$ticket_id = $rsvp_ticket->ID;
			}
			if ( $ticket_id > 0 ) {
				break;
			}
		}

		$attendee_details = [
			'full_name'    => $attendee_name,
			'email'        => $attendee_email,
			'order_status' => 'yes',
			'optout'       => false,
			'order_id'     => '-1',
		];

		$order  = new Tribe__Tickets__RSVP();
		$status = $order->generate_tickets_for( $ticket_id, $number_of_guests, $attendee_details );

		if ( ! $status ) {
			return false;
		}

		$context                     = [];
		$context['event_id']         = $selected_options['event_calendar_rsvp_event'];
		$context['attendee_name']    = $attendee_name;
		$context['attendee_email']   = $attendee_email;
		$context['number_of_guests'] = $number_of_guests;

		$event = tribe_get_event( $context['event_id'] );

		if ( $event ) {
			$context['event_name']               = $event->post_title;
			$context['event_url']                = get_permalink( $event->ID );
			$context['event_featured_image_id']  = get_post_meta( $event->ID, '_thumbnail_id', true );
			$context['event_featured_image_url'] = get_the_post_thumbnail_url( $event->ID );
		}

		return $context;
	}
}

EventCalendarSendRsvp::get_instance();
