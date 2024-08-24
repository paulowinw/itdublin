<?php
/**
 * WPFusion core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WPFusion;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WPFusion
 */
class WPFusion extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WPFusion';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WP Fusion', 'suretriggers' );
		$this->description = __( 'WP Fusion links WordPress with CRM.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/wp-fusion.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		if ( class_exists( 'WP_Fusion_Lite' ) || class_exists( 'WP_Fusion' ) ) {
			return true;
		} else {
			return false;
		}
	}
}

IntegrationsController::register( WPFusion::class );
