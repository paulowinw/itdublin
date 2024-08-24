<?php
/**
 * BookingStatusChanged.
 * php version 5.6
 *
 * @category BookingStatusChanged
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceBookings\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use WC_Booking;

if ( ! class_exists( 'BookingStatusChanged' ) ) :

	/**
	 * BookingStatusChanged
	 *
	 * @category BookingStatusChanged
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class BookingStatusChanged {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WoocommerceBookings';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wc_booking_status_changed';

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
				'label'         => __( 'Booking Created', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'woocommerce_booking_status_changed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param string $from       Previous status.
		 * @param string $to         New (current) status.
		 * @param int    $booking_id Booking id.
		 * @param object $booking    Booking object.
		 *
		 * @return void
		 */
		public function trigger_listener( $from, $to, $booking_id, $booking ) {

			if ( '' == $booking_id ) {
				return;
			}

			$was_in_cart = 'was-in-cart';
			if ( $to === $was_in_cart || $from === $was_in_cart ) {
				return;
			}

			if ( class_exists( 'WC_Booking' ) ) {
				$booking             = new WC_Booking( $booking_id );
				$person_counts       = $booking->get_person_counts();
				$bookable_product_id = $booking->get_product_id();
				if ( method_exists( $booking, 'get_data' ) ) {
					$booking          = $booking->get_data();
					$booking['start'] = gmdate( 'Y-m-d H:i:s', $booking['start'] );
					$booking['end']   = gmdate( 'Y-m-d H:i:s', $booking['end'] );
					if ( ! empty( $person_counts ) ) {
						$total_count = 0;
						foreach ( $person_counts as $key => $value ) {
							$total_count += $value;
						}
						$booking['total_person_counts'] = $total_count;
					}
					$booking['bookable_product'] = $bookable_product_id;
					$context                     = array_merge( $booking, WordPress::get_user_context( $booking['customer_id'] ) );
					$context['from_status']      = $from;
					$context['to_status']        = $to;
					
					AutomationController::sure_trigger_handle_trigger(
						[
							'trigger' => $this->trigger,
							'context' => $context,
						]
					);
				}
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	BookingStatusChanged::get_instance();

endif;
