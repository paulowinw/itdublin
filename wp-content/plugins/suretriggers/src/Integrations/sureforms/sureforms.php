<?php
/**
 * SureForms core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\SureForms;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use SRFM\Plugin_Loader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\SureForms
 */
class SureForms extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'SureForms';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'SureForms', 'suretriggers' );
		$this->description = __( 'A simple yet powerful way to create modern forms for your website.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/sureforms.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( Plugin_Loader::class );
	}

}

IntegrationsController::register( SureForms::class );
