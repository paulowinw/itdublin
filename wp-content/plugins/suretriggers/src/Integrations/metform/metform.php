<?php
/**
 * Met Form core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\MetForm;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\MetForm
 */
class MetForm extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'MetForm';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'MetForm', 'suretriggers' );
		$this->description = __( 'MetForm is a WordPress Form Builder.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/metform.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( \MetForm\Plugin::class );
	}

}

IntegrationsController::register( MetForm::class );
