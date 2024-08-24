<?php
/**
 * UserBookingApproved.
 * php version 5.6
 *
 * @category UserRegisterInEvent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EventsManager\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserBookingApproved' ) ) :

	/**
	 * PurchaseMembership
	 *
	 * @category PurchaseMembership
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserBookingApproved {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'EventsManager';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'em_user_booking_approved';

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
				'label'         => __( 'Booking Approved', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'em_booking_status_changed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 *
		 * @param object $em_booking_obj Event data.
		 * @param object $em_status Event booking status.
		 *  
		 * @return void
		 */
		public function trigger_listener( $em_booking_obj, $em_status ) {
			if ( 1 !== $em_status['status'] ) {
				return;
			}
			$event_id = $em_booking_obj->event_id;
			$user_id  = $em_booking_obj->person_id;
			global $wpdb;
		
			$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.event_id = %s", $event_id ) );
			$location     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_locations as b WHERE b.location_id  = %s", $all_bookings->location_id ) );
			$context      = array_merge(
				WordPress::get_user_context( $user_id ), 
				(array) json_decode( wp_json_encode( $all_bookings ), true )
			);
			if ( ! empty( $location ) ) {
				$context = array_merge( $context, (array) $location );
			}

			$context['post_id'] = $all_bookings->post_id;
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
	UserBookingApproved::get_instance();

endif;
