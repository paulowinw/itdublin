<?php
/**
 * UltimateMember core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\UltimateMember;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\UltimateMember
 */
class UltimateMember extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'UltimateMember';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'UltimateMember', 'suretriggers' );
		$this->description = __( 'A user profile plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/ultimatemember.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'UM' ) || defined( 'um_url' );
	}

}

IntegrationsController::register( UltimateMember::class );
