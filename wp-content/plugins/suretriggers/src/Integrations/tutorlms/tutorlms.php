<?php
/**
 * TutorLMS core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\TutorLMS;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\TutorLMS
 */
class TutorLMS extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'TutorLMS';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'TutorLMS', 'suretriggers' );
		$this->description = __( 'Easily Create And Sell Online Courses On Your WP Site With TutorLMS.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/tutorlms.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( '\TUTOR\Tutor' );
	}

}

IntegrationsController::register( TutorLMS::class );
