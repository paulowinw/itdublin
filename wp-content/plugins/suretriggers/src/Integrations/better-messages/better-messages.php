<?php
/**
 * Better Messages core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\BetterMessages;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use Better_Messages_Functions;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\BetterMessages
 */
class BetterMessages extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'BetterMessages';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Better Messages', 'suretriggers' );
		$this->description = __( 'Better Messages – is realtime private messaging system for WordPress, BuddyPress, BuddyBoss Platform, Ultimate Member, PeepSo and any other WordPress powered websites.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/better-messages.svg';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		if ( class_exists( 'Better_Messages_Functions' ) ) {
			return true;
		} else {
			return false;
		}
	}

}

IntegrationsController::register( BetterMessages::class );
