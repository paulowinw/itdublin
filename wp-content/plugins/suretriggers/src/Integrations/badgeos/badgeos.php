<?php
/**
 * BadgeOS core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\BadgeOS;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\BadgeOS
 */
class BadgeOS extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'BadgeOS';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'BadgeOS', 'suretriggers' );
		$this->description = __( 'BadgeOS lets your site’s users complete tasks and earn badges that recognize their achievement.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/badgeos.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'BadgeOS' );
	}
}

IntegrationsController::register( BadgeOS::class );
