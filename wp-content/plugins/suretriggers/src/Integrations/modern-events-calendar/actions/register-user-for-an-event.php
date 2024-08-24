<?php
/**
 * RegisterUserForAnEvent.
 * php version 5.6
 *
 * @category RegisterUserForAnEvent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ModernEventsCalendar\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\ModernEventsCalendar\ModernEventsCalendar;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
/**
 * RegisterUserForAnEvent
 *
 * @category RegisterUserForAnEvent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RegisterUserForAnEvent extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'ModernEventsCalendar';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mec_register_user_for_an_event';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Register User for an Event', 'suretriggers' ),
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
	 * @param array $selected_options selected_options.
	 *
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		// Check hard dependency for \MEC_gateway_pay_locally class if it exists.
		if ( ! class_exists( '\MEC_gateway_pay_locally' ) ) {
			return;
		}

		// Check hard dependency for \MEC_feature_books class if it exists.
		if ( ! class_exists( '\MEC_feature_books' ) ) {
			return;
		}

		$gateway  = new \MEC_gateway_pay_locally();
		$mec_book = new \MEC_feature_books();

		$event_id           = absint( sanitize_text_field( $selected_options['event_id'] ) );
		$selected_ticket_id = absint( sanitize_text_field( $selected_options['ticket_id'] ) );
		$wp_user_email      = sanitize_text_field( $selected_options['wp_user_email'] );

		if ( ! is_email( $wp_user_email ) ) {
			throw new Exception( 'Invalid user email.' );
		}

		$user = get_user_by( 'email', $wp_user_email );

		if ( ! $user ) {
			throw new Exception( 'User email not exists.' );
		}

		$book = $mec_book->getBook();

		$attendee = [
			'email' => $user->user_email,
			'name'  => $user->display_name,
			'reg'   => [],
		];

		// Generate new user id from gateway registration.
		$user_id = $gateway->register_user( $attendee );

		// The date.
		$event_date           = null;
		$event_date_from_meta = get_post_meta( $event_id, 'mec_date', true );

		// OCC Timestamp.
		if ( is_array( $event_date_from_meta ) && isset( $event_date_from_meta['start'] ) && isset( $event_date_from_meta['end'] ) ) {
			$event_date = $book->timestamp( $event_date_from_meta['start'], $event_date_from_meta['end'] );
		} else {
			// log error here.
			$error_message = 'Event Start Date and End Date is missing. Please check if the select Event has a corresponding dates.';
			throw new Exception( $error_message );
		}

		// The attendees count. We will set it to `1` since there can only be 1 logged-in user at a time.
		$attendees_count = 1;

		// The ticket ID.
		$tickets = [];

		// This will hold the comma separated value later on for the ticket IDs.
		$ticket_ids = '';

		for ( $i = 1; $i <= $attendees_count; $i ++ ) {
			$tickets[] = array_merge(
				$attendee,
				[
					'id'         => $selected_ticket_id,
					// MEC_SELECTED_TICKET_ID.
					'count'      => 1,
					'variations' => [],
					'reg'        => $attendee['reg'],
				]
			);

			$ticket_ids .= $selected_ticket_id . ',';
		}

		$raw_tickets   = [ $selected_ticket_id => $attendees_count ];
		$event_tickets = get_post_meta( $event_id, 'mec_tickets', true );

		// Calculate price of bookings.
		$price_details = $book->get_price_details( $raw_tickets, $event_id, $event_tickets, [] );

		// Configure the transaction.
		$transaction = [
			'tickets'       => $tickets,
			'date'          => $event_date,
			'event_id'      => $event_id,
			'price_details' => $price_details,
			'total'         => $price_details['total'],
			'discount'      => 0,
			'price'         => $price_details['total'],
			'coupon'        => null,
			'fields'        => [],
		];

		// Save The Transaction.
		$transaction_id = $book->temporary( $transaction );

		// Create new booking (CPT).
		$book_args = [
			'post_author' => $user_id,
			'post_type'   => 'mec-books',
			'post_title'  => sprintf( '%s - %s', $user->display_name, $user->user_email ),
		];

		$booking_id = $book->add( $book_args, $transaction_id, ',' . trim( $ticket_ids, ', ' ) . ',' );

		// Update the `mec_attendees`.
		update_post_meta( $booking_id, 'mec_attendees', $tickets );
		update_post_meta( $booking_id, 'mec_reg', $attendee['reg'] );
		update_post_meta( $booking_id, 'mec_gateway', 'MEC_gateway_pay_locally' );
		update_post_meta( $booking_id, 'mec_gateway_label', $gateway->title() );

		// For Booking Badge.
		update_post_meta( $booking_id, 'mec_book_date_submit', gmdate( 'YmdHis', time() ) );

		// Execute pending action.
		do_action( 'mec_booking_pended', $booking_id );

		// Send notification if it's a new booking.
		try {
			if ( $this->is_new_booking( $booking_id ) ) {
				do_action( 'mec_booking_completed', $booking_id );
			}
		} catch ( \Exception $e ) {
			throw new Exception( $e->getMessage() );
		}

		$context               = [];
		$context['booking_id'] = $booking_id;
		$context['ticket_id']  = $selected_ticket_id;

		return array_merge(
			WordPress::get_user_context( $user->ID ),
			ModernEventsCalendar::get_event_context( $event_id ),
			$context
		);
	}

	/**
	 * Check if booking is new or not.
	 *
	 * @param int $booking_id Booking ID.
	 * @return bool True if booking already exists. Otherwise, false.
	 * @throws \Exception Exception.
	 */
	public function is_new_booking( $booking_id = 0 ) {
		if ( empty( $booking_id ) ) {
			throw new \Exception( 'Booking ID is empty.' );
		}
		// Return true since the MEC action `Register the user for {event}` always registers a new booking.
		return true;
	}
}

RegisterUserForAnEvent::get_instance();
