<?php
/**
 * MyCred core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\MyCred;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\MyCred
 */
class MyCred extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'MyCred';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'MyCred', 'suretriggers' );
		$this->description = __( 'A free points management plugin for WordPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/mycred.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'myCRED_Core' );
	}

}

IntegrationsController::register( MyCred::class );
