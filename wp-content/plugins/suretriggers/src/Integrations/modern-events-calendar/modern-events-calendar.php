<?php
/**
 * ModernEventsCalendar core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\ModernEventsCalendar;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\ModernEventsCalendar
 */
class ModernEventsCalendar extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'ModernEventsCalendar';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Modern Events Calendar', 'suretriggers' );
		$this->description = __( 'Best WordPress Event Calendar Plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/moderneventscalendar.svg';

		parent::__construct();
	}

	/**
	 * Get Event context data.
	 *
	 * @param int $booking_id Event Booking ID.
	 *
	 * @return array
	 */
	public static function get_event_context( $booking_id ) {
		$event_id = absint( get_post_meta( $booking_id, 'mec_event_id', true ) );

		if ( ! $event_id ) {
			return [];
		}

		$event = get_post( $event_id );

		// Event booking info.
		$event_booking                    = [];
		$event_booking['title']           = get_the_title( $booking_id );
		$event_booking['transaction_id']  = get_post_meta( $booking_id, 'mec_transaction_id', true );
		$event_booking['amount_payable']  = get_post_meta( $booking_id, 'mec_payable', true );
		$event_booking['price']           = get_post_meta( $booking_id, 'mec_price', true );
		$event_booking['time']            = get_post_meta( $booking_id, 'mec_booking_time', true );
		$event_booking['payment_gateway'] = get_post_meta( $booking_id, 'mec_gateway_label', true );

		$is_confirmed                         = get_post_meta( $booking_id, 'mec_confirmed', true );
		$event_booking['confirmation_status'] = 'Pending';
		if ( 1 == $is_confirmed ) {
			$event_booking['confirmation_status'] = 'Confirmed';
		} elseif ( -1 == $is_confirmed ) {
			$event_booking['confirmation_status'] = 'Rejected';
		}

		$is_verified                          = get_post_meta( $booking_id, 'mec_verified', true );
		$event_booking['verification_status'] = 'Waiting';
		if ( 1 == $is_verified ) {
			$event_booking['verification_status'] = 'Verified';
		} elseif ( -1 == $is_verified ) {
			$event_booking['verification_status'] = 'Canceled';
		}

		// Event attendees.
		$event_attendees = [];
		$attendees       = get_post_meta( $booking_id, 'mec_attendees', true );
		if ( is_array( $attendees ) && ! empty( $attendees ) ) {
			$event_booking['attendees_count'] = count( $attendees );
			foreach ( $attendees as $attendee ) {
				$event_attendees[] = [
					'id'    => $attendee['id'],
					'email' => $attendee['email'],
					'name'  => $attendee['name'],
				];
			}
		}

		// Event tickets.
		$event_tickets = [];
		$tickets       = get_post_meta( $event_id, 'mec_tickets', true );
		if ( is_array( $tickets ) && ! empty( $tickets ) ) {
			foreach ( $tickets as $ticket ) {
				$event_tickets[] = [
					'id'          => $ticket['id'],
					'name'        => $ticket['name'],
					'description' => $ticket['description'],
					'price'       => $ticket['price'],
					'price_label' => $ticket['price_label'],
					'limit'       => $ticket['limit'],
				];
			}
		}

		// Start date time.
		$event_start_date_time = get_post_meta( $event_id, 'mec_start_datetime', true );

		$start_date = $event_start_date_time;
		$start_time = $event_start_date_time;

		if ( is_string( $event_start_date_time ) ) {
			$start_date = gmdate( 'F j, Y', (int) strtotime( $event_start_date_time ) );
			$start_time = gmdate( 'g:i A', (int) strtotime( $event_start_date_time ) );
		}

		// End date time.
		$event_end_date_time = get_post_meta( $event_id, 'mec_end_datetime', true );

		$end_date = $event_end_date_time;
		$end_time = $event_end_date_time;

		if ( is_string( $event_end_date_time ) ) {
			$end_date = gmdate( 'F j, Y', (int) strtotime( $event_end_date_time ) );
			$end_time = gmdate( 'g:i A', (int) strtotime( $event_end_date_time ) );
		}

		// Event categories.
		$event_categories = null;
		$categories       = get_the_terms( $event_id, 'mec_category' );

		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			$category_names = [];

			// Loop through the terms and collect category names.
			foreach ( $categories as $category ) {
				$category_names[] = $category->name;
			}

			// Create a comma-separated string of category names.
			$event_categories = implode( ', ', $category_names );
		}

		return [
			'event_id'           => $event_id,
			'title'              => get_the_title( $event_id ),
			'description'        => isset( $event->post_content ) ? $event->post_content : null,
			'categories'         => $event_categories,
			'start_date'         => $start_date,
			'start_time'         => $start_time,
			'end_date'           => $end_date,
			'end_time'           => $end_time,
			'location'           => self::get_event_location( $event_id ),
			'organizer'          => self::get_event_organizer( $event_id ),
			'cost'               => get_post_meta( $event_id, 'mec_cost', true ),
			'featured_image_id'  => get_post_thumbnail_id( $event_id ),
			'featured_image_url' => get_the_post_thumbnail_url( $event_id ),
			'tickets'            => $event_tickets,
			'attendees'          => $event_attendees,
			'booking'            => $event_booking,
		];
	}

	/**
	 * Returns the Event Organizer.
	 *
	 * @param int $event_id Event ID.
	 * @return string|null
	 */
	public static function get_event_organizer( $event_id ) {
		$organizer_id = get_post_meta( $event_id, 'mec_organizer_id', true );

		if ( empty( $organizer_id ) || ! is_numeric( $organizer_id ) ) {
			return null;
		}

		$organizer_term = get_term( (int) $organizer_id, 'mec_organizer' );

		if ( is_wp_error( $organizer_term ) || ! $organizer_term || ! isset( $organizer_term->name ) ) {
			return null;
		}

		return $organizer_term->name;
	}

	/**
	 * Returns the location of the event.
	 *
	 * @param int $event_id Event ID.
	 * @return string|null
	 */
	public static function get_event_location( $event_id ) {
		$location_id = get_post_meta( $event_id, 'mec_location_id', true );

		if ( empty( $location_id ) || ! is_numeric( $location_id ) ) {
			return null;
		}
		
		$location_term = get_term( (int) $location_id, 'mec_location' );

		if ( is_wp_error( $location_term ) || ! $location_term || ! isset( $location_term->name ) ) {
			return null;
		}

		return $location_term->name;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'MEC_ABSPATH' );
	}
}

IntegrationsController::register( ModernEventsCalendar::class );
