<?php
/**
 * Gravity Kit core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\GravityKit;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\GravityKit
 */
class GravityKit extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'GravityKit';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Gravity Kit', 'suretriggers' );
		$this->description = __( 'Gravity Kit is a WordPress Plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/gravitykit.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		if ( class_exists( 'GravityView_Plugin' ) && class_exists( 'GFFormsModel' ) ) {
			return true;
		} else {
			return false;
		}
	}

}

IntegrationsController::register( GravityKit::class );
