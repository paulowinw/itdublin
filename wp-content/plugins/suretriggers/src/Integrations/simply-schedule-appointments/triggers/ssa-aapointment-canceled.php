<?php
/**
 * SsaAppointmentCanceled.
 * php version 5.6
 *
 * @category SsaAppointmentCanceled
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SimplyScheduleAppointments\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'SsaAppointmentCanceled' ) ) :

	/**
	 * SsaAppointmentCanceled
	 *
	 * @category SsaAppointmentCanceled
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class SsaAppointmentCanceled {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'SimplyScheduleAppointments';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ssa_appointment_canceled';

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
				'common_action' => 'ssa/appointment/canceled',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int   $appointment_id       appointment id.
		 * @param array $data_after       Booking details.
		 * @param array $data_before Old Booking details.
		 * @param array $response Response.
		 *
		 * @return void
		 */
		public function trigger_listener( $appointment_id, $data_after, $data_before, $response ) {
			
			global $wpdb;
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ssa_appointments where 
            id=%d",
					$appointment_id 
				),
				ARRAY_A 
			);
			if ( ! empty( $result ) ) {
				$result_meta = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}ssa_appointment_meta
                 where appointment_id=%d",
						$result['id'] 
					) 
				);
				if ( ! empty( $result_meta ) ) {
					foreach ( $result_meta as $meta ) {
						$result[ $meta->meta_key ] = $meta->meta_value;
					}
				}
			}
			$result['customer_information'] = json_decode( $result['customer_information'], true );
			$context                        = $result;
			
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
	SsaAppointmentCanceled::get_instance();

endif;
