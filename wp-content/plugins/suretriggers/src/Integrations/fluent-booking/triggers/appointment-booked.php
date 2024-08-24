<?php
/**
 * AppointmentBooked.
 * php version 5.6
 *
 * @category AppointmentBooked
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentBooking\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'AppointmentBooked' ) ) :

	/**
	 * AppointmentBooked
	 *
	 * @category AppointmentBooked
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AppointmentBooked {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'FluentBooking';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'fluent_booking_new_appointment_booked';

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
				'label'         => __( 'New Appointment Booked', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'fluent_booking/after_booking_scheduled',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $booking       Booking details.
		 * @param array $calendar_event Booking slot details.
		 *
		 * @return void
		 */
		public function trigger_listener( $booking, $calendar_event ) {
			
			if ( empty( $booking ) ) {
				return;
			}
			if ( 'scheduled' !== $booking['status'] ) {
				return;
			}

			$booking_data                  = $booking;
			$booking_data['custom_fields'] = $booking->getCustomFormData( false );
			$booking_array                 = [
				'event_id' => $calendar_event['id'],
				'booking'  => $booking_data,
				'event'    => $calendar_event,
			];
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $booking_array,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	AppointmentBooked::get_instance();

endif;
