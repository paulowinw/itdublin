<?php
/**
 * Groundhogg core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\Groundhogg;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\Groundhogg
 */
class Groundhogg extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'Groundhogg';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Groundhogg', 'suretriggers' );
		$this->description = __( 'Groundhogg is the best WordPress CRM & Marketing Automation plugin. Create funnels, email campaigns, newsletters, marketing automation.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/Groundhogg.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'GROUNDHOGG_VERSION' );
	}

}

IntegrationsController::register( Groundhogg::class );
