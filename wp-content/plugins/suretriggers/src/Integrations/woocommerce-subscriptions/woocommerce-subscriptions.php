<?php
/**
 * WoocommerceSubscriptions core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WoocommerceSubscriptions;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WoocommerceSubscriptions
 */
class WoocommerceSubscriptions extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WoocommerceSubscriptions';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WoocommerceSubscriptions', 'suretriggers' );
		$this->description = __( 'Woocommerce subscriptions plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/woocommercesubscriptions.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WooCommerce' ) && class_exists( 'WC_Subscriptions' );
	}

}

IntegrationsController::register( WoocommerceSubscriptions::class );
