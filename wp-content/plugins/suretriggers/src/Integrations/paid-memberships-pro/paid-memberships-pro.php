<?php
/**
 * PaidMembershipsPro core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\PaidMembershipsPro;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\PaidMembershipsPro
 */
class PaidMembershipsPro extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'PaidMembershipsPro';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'PaidMembershipsPro', 'suretriggers' );
		$this->description = __( 'A tool that help to start, manage and grow membership.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/paidmembershipspro.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'PMPRO_BASE_FILE' );
	}

}

IntegrationsController::register( PaidMembershipsPro::class );
