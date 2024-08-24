<?php
/**
 * Asgaros core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\Asgaros;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WPForo
 */
class Asgaros extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'Asgaros';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Asgaros', 'suretriggers' );
		$this->description = __( 'The best WordPress forum plugin, full-fledged yet easy and light forum solution for your WordPress website. The only forum software with multiple forum layouts.', 'suretriggers' );

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'AsgarosForum' );
	}
}

IntegrationsController::register( Asgaros::class );
