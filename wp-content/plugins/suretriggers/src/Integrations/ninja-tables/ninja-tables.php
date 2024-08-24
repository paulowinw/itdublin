<?php
/**
 * Ninja Tables core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\NinjaTables;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\NinjaTables
 */
class NinjaTables extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'NinjaTables';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Ninja Tables', 'suretriggers' );
		$this->description = __( 'Best Data Table Plugin for WordPress.', 'suretriggers' );
		parent::__construct();
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'NINJA_TABLES_VERSION' );
	}

}

IntegrationsController::register( NinjaTables::class );
