<?php
/**
 * Event Calendar integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\TheEventCalendar;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\EventCalendar
 */
class TheEventCalendar extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'TheEventCalendar';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'The Events Calendar', 'suretriggers' );
		$this->description = __( 'Easily create and manage an events calendar on your WordPress site with The Events Calendar plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/the-events-calendar.svg';

		parent::__construct();
	}

	/**
	 * Fetch event context.
	 *
	 * @param int $product_id product id.
	 * @param int $order_id order id.
	 * @return array
	 */
	public static function get_event_context( $product_id, $order_id ) {
		if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
			return [];
		}

		$event     = tribe_events_get_ticket_event( $product_id );
		$attendees = tribe_tickets_get_attendees( $order_id );

		if ( ! $event || ! $attendees ) {
			return [];
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

		return [
			'event_id'  => $event->ID,
			'event'     => $event,
			'attendies' => $attendee_details,
		];
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'Tribe__Tickets__Main' ) && class_exists( 'Tribe__Events__Main' );
	}
}

IntegrationsController::register( TheEventCalendar::class );
