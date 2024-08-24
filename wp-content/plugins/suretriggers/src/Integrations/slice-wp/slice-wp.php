<?php
/**
 * SliceWP core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\SliceWP;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\SliceWP
 */
class SliceWP extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'SliceWP';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'SliceWP', 'suretriggers' );
		$this->description = __( 'Affiliate Plugin for WordPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/affiliatewp.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin dependent plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'SLICEWP_VERSION' );
	}

}

IntegrationsController::register( SliceWP::class );
