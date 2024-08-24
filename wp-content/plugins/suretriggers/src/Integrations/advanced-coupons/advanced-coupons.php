<?php
/**
 * Advanced Coupons core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\AdvancedCoupons;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\AdvancedCoupons
 */
class AdvancedCoupons extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'AdvancedCoupons';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Advanced Coupons', 'suretriggers' );
		$this->description = __( 'Advanced coupons for Woocommerce', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/AdvancedCoupons.svg';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'ACFWF' );
	}

}

IntegrationsController::register( AdvancedCoupons::class );
