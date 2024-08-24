<?php
/**
 * UnregisterUserFromEvent.
 * php version 5.6
 *
 * @category UnregisterUserFromEvent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * UnregisterUserFromEvent
 *
 * @category UnregisterUserFromEvent
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UnregisterUserFromEvent extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'EventsManager';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'em_unregister_user_from_event';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Unregister the user from an event', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! $user_id ) {
			throw new Exception( 'User not found with this email address.' );
		}
		$event_id = $selected_options['event_id'];

		global $wpdb;
	
		if ( '-1' === $event_id ) {
			$all_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT b.booking_id,e.* FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND b.person_id = %s AND e.event_end_date >= CURRENT_DATE", $user_id ) );

		} else {
			$event_id_id  = get_post_meta( $event_id, '_event_id', true );
			$all_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT b.booking_id,e.* FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.event_id = %s AND b.booking_status NOT IN (2,3) AND b.person_id = %s AND e.event_end_date >= CURRENT_DATE", $event_id_id, $user_id ) );
		}

		if ( empty( $all_bookings ) ) {
			throw new Exception( 'The user was not registered for the specified event.' ); 
		}

		foreach ( $all_bookings as $booking ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}em_bookings SET booking_status= 3 WHERE booking_id=%d", $booking->booking_id ) );
		}

		$context = $all_bookings;

		return $context;
	}

}

UnregisterUserFromEvent::get_instance();
