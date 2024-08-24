<?php
/**
 * Forminator core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\Forminator;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\Forminator
 */
class Forminator extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'Forminator';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Forminator', 'suretriggers' );
		$this->description = __( 'A form builder plugin. ', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/Forminator.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'Forminator' );
	}

}

IntegrationsController::register( Forminator::class );
