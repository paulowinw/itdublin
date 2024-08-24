<?php
/**
 * TriggerButton core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\TriggerButton;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\TriggerButton
 */
class TriggerButton extends Integrations {



	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'TriggerButton';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Trigger Button', 'suretriggers' );
		$this->description = __( 'A Trigger Button to complete the automation.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/triggerbutton.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return true;
	}

}

IntegrationsController::register( TriggerButton::class );
