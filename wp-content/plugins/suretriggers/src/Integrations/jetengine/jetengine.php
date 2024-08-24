<?php
/**
 * JetEngine core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\JetEngine;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\JetEngine
 */
class JetEngine extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'JetEngine';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'JetEngine', 'suretriggers' );
		$this->description = __(
			'WordPress Dynamic Content Plugin for
		Elementor.',
			'suretriggers' 
		);
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/jetengine.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( '\Jet_Engine' );
	}

}

IntegrationsController::register( JetEngine::class );
