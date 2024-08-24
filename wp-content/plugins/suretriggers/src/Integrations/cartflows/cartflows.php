<?php
/**
 * CartFlows core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\CartFlows;

use Cartflows_Loader;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\CartFlows
 */
class CartFlows extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'CartFlows';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'CartFlows', 'suretriggers' );
		$this->description = __( 'Create beautiful checkout pages & sales flows for WooCommerce.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/cartflows.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( Cartflows_Loader::class );
	}
}

IntegrationsController::register( CartFlows::class );
