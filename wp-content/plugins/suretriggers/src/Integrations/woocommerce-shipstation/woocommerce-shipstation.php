<?php
/**
 * WoocommerceShipstation core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WoocommerceShipstation;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WoocommerceShipstation
 */
class WoocommerceShipstation extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WoocommerceShipstation';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WoocommerceShipstation', 'suretriggers' );
		$this->description = __( 'Woocommerce shipstation plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/woocommerceshipstation.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WooCommerce' );
	}

}

IntegrationsController::register( WoocommerceShipstation::class );
