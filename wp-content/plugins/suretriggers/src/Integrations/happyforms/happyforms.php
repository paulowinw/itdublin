<?php
/**
 * HappyForms core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\HappyForms;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\HappyForms
 */
class HappyForms extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'HappyForms';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'HappyForms', 'suretriggers' );
		$this->description = __( 'A contact form builder plugin. ', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/happyforms.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'HappyForms' );
	}

}

IntegrationsController::register( HappyForms::class );
