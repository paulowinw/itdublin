<?php
/**
 * GiveWP integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\GiveWP;

use Give;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\GiveWP
 */
class GiveWP extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'GiveWP';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'GiveWP', 'suretriggers' );
		$this->description = __( 'GiveWP is an evolving WordPress donation plugin with a team that genuinely cares about advancing the democratization of generosity.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/givewp.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( Give::class );
	}

}

IntegrationsController::register( GiveWP::class );
