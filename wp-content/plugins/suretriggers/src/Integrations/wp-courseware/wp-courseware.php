<?php
/**
 * WPCourseware core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WPCourseware;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WPCourseware
 */
class WPCourseware extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WPCourseware';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WP Courseware', 'suretriggers' );
		$this->description = __( 'WP Courseware is a popular WordPress plugin for creating and managing online courses. It allows you to easily organize course content, track student progress, and create engaging learning experiences on your WordPress website.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/wp-courseware.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WPCW_Requirements' );
	}
}

IntegrationsController::register( WPCourseware::class );
