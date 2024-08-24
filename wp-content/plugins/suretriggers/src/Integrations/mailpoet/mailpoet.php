<?php
/**
 * MailPoet core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\MailPoet;

use MailPoet\Plugin;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\MailPoet
 */
class MailPoet extends Integrations {



	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'MailPoet';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'MailPoet', 'suretriggers' );
		$this->description = __( 'A WordPress plugin for emails and newletters.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/mailpoet.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( '\MailPoet\Config\Activator' );
	}

}

IntegrationsController::register( MailPoet::class );
