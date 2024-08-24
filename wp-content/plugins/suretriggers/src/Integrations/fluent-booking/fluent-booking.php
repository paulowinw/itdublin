<?php
/**
 * FluentBooking core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\FluentBooking;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\FluentBooking
 */
class FluentBooking extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'FluentBooking';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'FluentBooking', 'suretriggers' );
		$this->description = __( 'FluentBooking is the Ultimate Scheduling Solution for WordPress. Harness the power of unlimited appointments, bookings, webinars, events, sales calls, etc., and save time with scheduling automation.', 'suretriggers' );
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'FLUENT_BOOKING_VERSION' );
	}

	

}

IntegrationsController::register( FluentBooking::class );
