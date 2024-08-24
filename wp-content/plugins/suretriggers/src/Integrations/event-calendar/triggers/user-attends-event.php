<?php
/**
 * UserAttendsEvent.
 * php version 5.6
 *
 * @category UserAttendsEvent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EventCalendar\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\TheEventCalendar\TheEventCalendar;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserAttendsEvent' ) ) :

	/**
	 * UserAttendsEvent
	 *
	 * @category UserAttendsEvent
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserAttendsEvent {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'TheEventCalendar';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_attends_event';

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
				'label'         => __( 'User Attends Event', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [
					'event_tickets_checkin',
					'eddtickets_checkin',
					'rsvp_checkin',
					'wootickets_checkin',
				],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $attendee_id Attendee id.
		 * @param object $qr QR code data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $attendee_id, $qr ) {
			if ( ! $attendee_id ) {
				return;
			}
			if ( ! function_exists( 'tribe_tickets_get_attendees' ) ) {
				return;
			}
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$attendee_details = tribe_tickets_get_attendees( $attendee_id, 'rsvp_order' );
			if ( empty( $attendee_details ) ) {
				return;
			}
	
			$attendee = false;
			foreach ( $attendee_details as $detail ) {
				if ( (int) $detail['attendee_id'] !== (int) $attendee_id ) {
					continue;
				}
				$attendee = $detail;
			}
	
			if ( ! $attendee ) {
				return;
			}
	
			$attendee_user = get_user_by( 'email', $attendee['holder_email'] );
			if ( ! $attendee_user ) {
				return;
			}
			
			$product_id = get_post_meta( $attendee_id, '_tribe_rsvp_product', true );
			$order_id   = get_post_meta( $attendee_id, '_tribe_rsvp_order', true );
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$event_context = TheEventCalendar::get_event_context( $product_id, $order_id );
			$context       = array_merge( $attendee, $event_context );

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
	UserAttendsEvent::get_instance();

endif;
