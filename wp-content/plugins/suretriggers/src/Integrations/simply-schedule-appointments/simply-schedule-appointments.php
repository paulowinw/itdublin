<?php
/**
 * SimplyScheduleAppointments core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\SimplyScheduleAppointments;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\SimplyScheduleAppointments
 */
class SimplyScheduleAppointments extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'SimplyScheduleAppointments';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'SimplyScheduleAppointments', 'suretriggers' );
		$this->description = __( 'Simply Schedule Appointments Booking Plugin is for Consultants and Small Businesses using WordPress.', 'suretriggers' );
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'Simply_Schedule_Appointments' );
	}

	

}

IntegrationsController::register( SimplyScheduleAppointments::class );
