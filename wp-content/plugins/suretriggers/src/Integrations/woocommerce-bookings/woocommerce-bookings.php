<?php
/**
 * WoocommerceBookings core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WoocommerceBookings;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WoocommerceBookings
 */
class WoocommerceBookings extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WoocommerceBookings';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Woocommerce Bookings', 'suretriggers' );
		$this->description = __( 'WooCommerce Bookings is an extension for WooCommerce that allow customers to book appointments, make reservations or rent equipment without leaving your site.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/woocommercebookings.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WooCommerce' ) && class_exists( 'WC_Bookings' );
	}

}

IntegrationsController::register( WoocommerceBookings::class );
