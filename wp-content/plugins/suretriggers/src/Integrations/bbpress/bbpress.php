<?php
/**
 * BbPress integration class file
 *
 * @package  SureTriggers
 * @since 1.0.0
 */

namespace SureTriggers\Integrations\BbPress;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class BuddyBoss
 *
 * @package SureTriggers\Integrations\BbPress
 */
class BbPress extends Integrations {

	use SingletonLoader;

	/**
	 * ID of the integration
	 *
	 * @var string
	 */
	protected $id = 'bbPress';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'bbPress', 'suretriggers' );
		$this->description = __( 'Discussion forums for WordPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/bbpress.png';

		parent::__construct();
	}

	/**
	 * Check plugin is installed.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'bbPress' );
	}
}

IntegrationsController::register( BbPress::class );
