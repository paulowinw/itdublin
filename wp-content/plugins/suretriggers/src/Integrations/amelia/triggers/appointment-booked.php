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

namespace SureTriggers\Integrations\Amelia\Triggers;

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
		public $integration = 'Amelia';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'amelia_new_appointment_booked';

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
				'common_action' => 'AmeliaBookingAddedBeforeNotify',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];
			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param array $args Appointment Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $args ) {
			if ( empty( $args ) ) {
				return;
			}

			if ( 'appointment' !== $args['type'] ) {
				return;
			}

			global $wpdb;

			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT appointments.*, customer.* 
					FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
					INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments 
					ON customer.appointmentId=appointments.id 
					WHERE customer.appointmentId = %d AND appointments.serviceId = %d',
					[ $args['booking']['appointmentId'], $args['appointment']['serviceId'] ]
				),
				ARRAY_A
			);

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $args['booking']['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $args['booking']['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $args['appointment']['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( isset( $args['booking']['coupon'] ) ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $args['booking']['coupon']['id'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context                        = array_merge( $result, $fields_arr, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['amelia_service_list'] = $args['appointment']['serviceId'];

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
	AppointmentBooked::get_instance();

endif;
