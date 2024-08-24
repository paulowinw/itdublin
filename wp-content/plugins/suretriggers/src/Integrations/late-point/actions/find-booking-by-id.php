<?php
/**
 * FindBookingById.
 * php version 5.6
 *
 * @category FindBookingById
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LatePoint\Actions;

use OsBookingModel;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LatePoint\LatePoint;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * FindBookingById
 *
 * @category FindBookingById
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FindBookingById extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LatePoint';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lp_find_booking_by_id';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Find Booking By ID', 'suretriggers' ),
			'action'   => 'lp_find_booking_by_id',
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
	 *
	 * @throws Exception Exception.
	 *
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'OsBookingModel' ) ) {
			throw new Exception( 'LatePoint plugin not installed.' );
		}

		$booking_id = isset( $selected_options['booking_id'] ) ? $selected_options['booking_id'] : null;

		if ( ! $booking_id ) {
			throw new Exception( 'Booking ID not provided.' );
		}

		$booking = new OsBookingModel( $booking_id );

		$booking_data          = [];
		$booking_data['found'] = 'no';

		if ( isset( $booking->id ) && ! empty( $booking->id ) ) {
			$booking_data          = $booking->get_data_vars();
			$booking_data['found'] = 'yes';
		}

		return $booking_data;
	}

}

FindBookingById::get_instance();
