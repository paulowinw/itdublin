<?php
/**
 * EasyAffiliate core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\EasyAffiliate;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\EasyAffiliate
 */
class EasyAffiliate extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'EasyAffiliate';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Easy Affiliate', 'suretriggers' );
		$this->description = __( 'Affiliate Program Plugin for WordPress', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/easyaffiliate.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'ESAF_PLUGIN_SLUG' );
	}
}

IntegrationsController::register( EasyAffiliate::class );
