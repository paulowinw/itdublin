<?php
/**
 * WPLoyalty integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WPLoyalty;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WPLoyalty
 */
class WPLoyalty extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WPLoyalty';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WPLoyalty', 'suretriggers' );
		$this->description = __(
			'The best WordPress forum plugin, 
		full-fledged yet easy and light forum solution for your WordPress website. 
		The only forum software with multiple forum layouts.',
			'suretriggers' 
		);
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/wployalty.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WooCommerce' ) && class_exists( '\Wlr\App\Router' );
	}
}

IntegrationsController::register( WPLoyalty::class );
