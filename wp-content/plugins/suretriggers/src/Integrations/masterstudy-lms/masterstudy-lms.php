<?php
/**
 * MasterStudyLms core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\MasterStudyLms;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\MasterStudyLms
 */
class MasterStudyLms extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'MasterStudyLms';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'MasterStudyLms', 'suretriggers' );
		$this->description = __( 'A WordPress LMS Plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/masterstudylms.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		if ( in_array( 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			// Plugin is active and installed.
			return true;
		} else {
			// Plugin is not active or installed.
			return false;
		}
	}

}

IntegrationsController::register( MasterStudyLms::class );
