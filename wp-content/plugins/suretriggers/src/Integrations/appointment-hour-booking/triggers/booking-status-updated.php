<?php
/**
 * BookingStatusUpdated.
 * php version 5.6
 *
 * @category BookingStatusUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AppointmentHourBooking\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'BookingStatusUpdated' ) ) :

	/**
	 * BookingStatusUpdated
	 *
	 * @category BookingStatusUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class BookingStatusUpdated {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AppointmentHourBooking';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ahb_booking_status_updated';

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
				'label'         => __( 'Booking Status Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'cpappb_update_status',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $id Appointment ID.
		 * @param string $status Appointment Status.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $id, $status ) {
			
			global $wpdb;
			$events      = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}cpappbk_messages 
            WHERE id=%d",
					$id
				) 
			); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$posted_data = unserialize( $events[0]->posted_data );
			$context     = $posted_data;

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
	BookingStatusUpdated::get_instance();

endif;
