<?php
/**
 * WoocommerceMemberships core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WoocommerceMemberships;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WoocommerceMemberships
 */
class WoocommerceMemberships extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WoocommerceMemberships';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WoocommerceMemberships', 'suretriggers' );
		$this->description = __( 'Woocommerce memberships plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/woocommercememberships.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WooCommerce' ) && class_exists( 'WC_Memberships_Loader' );
	}

}

IntegrationsController::register( WoocommerceMemberships::class );
