<?php
/**
 * SureMembers core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\SureMembers;

use SureMembers\Plugin_Loader;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\SureMembers
 */
class SureMembers extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'SureMembers';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'SureMembers', 'suretriggers' );
		$this->description = __( 'A simple yet powerful way to add content restriction to your website.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/suremembers.svg';

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

IntegrationsController::register( SureMembers::class );
