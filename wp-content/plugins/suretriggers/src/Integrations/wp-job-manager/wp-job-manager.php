<?php
/**
 * WPJobManager core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WPJobManager;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use WP_Job_Manager;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WPJobManager
 */
class WPJobManager extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WPJobManager';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WPJobManager', 'suretriggers' );
		$this->description = __( 'Manage job listings from the WordPress admin panel.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/wp-job-manager.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( WP_Job_Manager::class );
	}
}

IntegrationsController::register( WPJobManager::class );
