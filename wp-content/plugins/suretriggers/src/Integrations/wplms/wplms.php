<?php
/**
 * WPLMS core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WPLMS;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WPLMS
 */
class WPLMS extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WPLMS';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WPLMS', 'suretriggers' );
		$this->description = __( 'WPLMS is a social network plugin for WordPress that allows you to quickly add a social network.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/WPLMS.svg';

		parent::__construct();
	}

	/**
	 * Get customer context data.
	 *
	 * @param int $course_id course.
	 *
	 * @return array
	 */
	public static function get_wplms_course_context( $course_id ) {
		$courses                       = get_post( $course_id );
		$context['wplms_course']       = $courses->ID;
		$context['wplms_course_name']  = $courses->post_name;
		$context['wplms_course_title'] = $courses->post_title;
		return $context;
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WPLMS_Init' );
	}

}

IntegrationsController::register( WPLMS::class );
