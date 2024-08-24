<?php
/**
 * JetFormBuilder core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\JetFormBuilder;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\JetFormBuilder
 */
class JetFormBuilder extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'JetFormBuilder';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'JetFormBuilder', 'suretriggers' );
		$this->description = __( 'A dynamic form creation tool. ', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/JetFormBuilder.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return function_exists( 'jet_form_builder_init' );
	}

}

IntegrationsController::register( JetFormBuilder::class );
