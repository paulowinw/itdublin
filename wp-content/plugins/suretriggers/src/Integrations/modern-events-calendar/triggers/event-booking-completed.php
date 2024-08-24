<?php
/**
 * EventBookingCompleted.
 * php version 5.6
 *
 * @category EventBookingCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ModernEventsCalendar\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\ModernEventsCalendar\ModernEventsCalendar;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'EventBookingCompleted' ) ) :

	/**
	 * EventBookingCompleted
	 *
	 * @category EventBookingCompleted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class EventBookingCompleted {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ModernEventsCalendar';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'mec_event_booking_completed';

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
				'label'         => __( 'Event Booking Completed', 'suretriggers' ),
				'action'        => 'mec_event_booking_completed',
				'common_action' => 'mec_booking_completed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $booking_id Booking ID.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function trigger_listener( $booking_id ) {
			if ( ! $booking_id ) {
				return;
			}

			$context = ModernEventsCalendar::get_event_context( $booking_id );

			if ( ! $context ) {
				return;
			}

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
	EventBookingCompleted::get_instance();

endif;
