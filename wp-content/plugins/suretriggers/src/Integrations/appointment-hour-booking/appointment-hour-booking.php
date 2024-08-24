<?php
/**
 * AppointmentHourBooking core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\AppointmentHourBooking;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\AppointmentHourBooking
 */
class AppointmentHourBooking extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'AppointmentHourBooking';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Appointment Hour Booking', 'suretriggers' );
		$this->description = __( 'A WordPress Booking plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/appointment-hour-booking.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'CP_AppBookingPlugin' );
	}

}

IntegrationsController::register( AppointmentHourBooking::class );
