<?php
/**
 * WPForo core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WPForo;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WPForo
 */
class WPForo extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'wpForo';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'wpForo', 'suretriggers' );
		$this->description = __( 'The best WordPress forum plugin, full-fledged yet easy and light forum solution for your WordPress website. The only forum software with multiple forum layouts.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/wpforo.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'WPFORO_VERSION' );
	}
}

IntegrationsController::register( WPForo::class );
