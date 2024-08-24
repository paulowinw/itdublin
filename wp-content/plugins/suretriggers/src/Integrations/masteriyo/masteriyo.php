<?php
/**
 * Masteriyo core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\Masteriyo;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\Masteriyo
 */
class Masteriyo extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'Masteriyo';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Masteriyo', 'suretriggers' );
		$this->description = __( 'A WordPress LMS Plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/masteriyo.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return function_exists( 'masteriyo' );
	}

}

IntegrationsController::register( Masteriyo::class );
