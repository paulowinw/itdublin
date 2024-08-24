<?php
/**
 * AttendeeRegisteredWC.
 * php version 5.6
 *
 * @category AttendeeRegisteredWC
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

if ( ! class_exists( 'AttendeeRegisteredWC' ) ) :

	/**
	 * AttendeeRegisteredWC
	 *
	 * @category AttendeeRegisteredWC
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AttendeeRegisteredWC {

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
		public $trigger = 'attendee_registered_wc';

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
				'label'         => __( 'Attendee Registered with WC', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'tribe_tickets_attendee_repository_create_attendee_for_ticket_after_create',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $attendee      The attendee object.
		 * @param array  $attendee_data List of additional attendee data.
		 * @param object $ticket        The ticket object.
		 * @param object $repository    The current repository object.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $attendee, $attendee_data, $ticket, $repository ) {

			if ( is_object( $ticket ) && property_exists( $ticket, 'ID' ) ) {
				$product_id                 = $ticket->ID;
				$order_id                   = $attendee_data['order_id'];
				$context                    = TheEventCalendar::get_event_context( $product_id, $order_id );
				$context['event_ticket_id'] = $ticket->ID;
	
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	AttendeeRegisteredWC::get_instance();

endif;
