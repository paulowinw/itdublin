<?php
/**
 * ContactForm7 core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\ContactForm7;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\ContactForm7
 */
class ContactForm7 extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'ContactForm7';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Contact Form7', 'suretriggers' );
		$this->description = __( 'A WordPress plugin of form submission', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/ContactForm7.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WPCF7' );
	}

}

IntegrationsController::register( ContactForm7::class );
