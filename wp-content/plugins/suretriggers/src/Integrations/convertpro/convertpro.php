<?php
/**
 * ConvertPro core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\ConvertPro;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\ConvertPro
 */
class ConvertPro extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'ConvertPro';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'ConvertPro', 'suretriggers' );
		$this->description = __( 'A WordPress plugin to convert visitors into leads, subscribers and customers. ', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/convertpro.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( '\Cp_V2_Loader' );
	}

}

IntegrationsController::register( ConvertPro::class );
