<?php
/**
 * LifterLMS core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\LifterLMS;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\LifterLMS
 */
class LifterLMS extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'LifterLMS';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'LifterLMS', 'suretriggers' );
		$this->description = __( 'Easily Create And Sell Online Courses On Your WP Site With LifterLMS.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/lifterlms.svg';

		parent::__construct();
	}

	/**
	 * Get course context data.
	 *
	 * @param int $course_id course.
	 *
	 * @return array
	 */
	public static function get_lms_course_context( $course_id ) {

		$courses                 = get_post( $course_id );
		$context['course']       = $courses->ID;
		$context['course_name']  = $courses->post_name;
		$context['course_title'] = $courses->post_title;
		$context['course_url']   = get_permalink( $course_id );
		return $context;
	}

	/**
	 * Get lesson context data.
	 *
	 * @param int $lesson_id lesson.
	 *
	 * @return array
	 */
	public static function get_lms_lesson_context( $lesson_id ) {

		$lesson                  = get_post( $lesson_id );
		$context['lesson']       = $lesson->ID;
		$context['lesson_name']  = $lesson->post_name;
		$context['lesson_title'] = $lesson->post_title;
		$context['lesson_url']   = get_permalink( $lesson_id );
		return $context;
	}

	/**
	 * Get membership context data.
	 *
	 * @param int $membership_id membership.
	 *
	 * @return array
	 */
	public static function get_lms_membership_context( $membership_id ) {

		$membership                  = get_post( $membership_id );
		$context['membership']       = $membership->ID;
		$context['membership_name']  = $membership->post_name;
		$context['membership_title'] = $membership->post_title;
		$context['membership_url']   = get_permalink( $membership_id );
		return $context;
	}


	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'LifterLMS' );
	}

}

IntegrationsController::register( LifterLMS::class );
