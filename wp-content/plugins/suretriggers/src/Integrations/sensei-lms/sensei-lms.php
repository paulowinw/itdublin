<?php
/**
 * SenseiLMS core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\SenseiLMS;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\SenseiLMS
 */
class SenseiLMS extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'SenseiLMS';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Sensei LMS', 'suretriggers' );
		$this->description = __( 'Learning Management System', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/tutorlms.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'Sensei_Main' );
	}

}

IntegrationsController::register( SenseiLMS::class );
