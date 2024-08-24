<?php
/**
 * Events Manager integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\EventsManager;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\EventCalendar
 */
class EventsManager extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'EventsManager';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Events Manager', 'suretriggers' );
		$this->description = __( 'Easy event registration.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/the-events-calendar.svg';

		parent::__construct();
	}
	
	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		if ( class_exists( 'EM_Events' ) ) {
			return true;
		} else {
			return false;
		}
	}

}

IntegrationsController::register( EventsManager::class );
