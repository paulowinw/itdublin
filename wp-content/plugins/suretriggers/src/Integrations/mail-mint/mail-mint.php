<?php
/**
 * MailMint core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\MailMint;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\MailMint
 */
class MailMint extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'MailMint';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name = __( 'MailMint', 'suretriggers' );
		parent::__construct();
	}


	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'MAILMINT' );
	}

}

IntegrationsController::register( MailMint::class );
