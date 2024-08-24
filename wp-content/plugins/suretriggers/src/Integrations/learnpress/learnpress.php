<?php
/**
 * LearnPress core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\LearnPress;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\LearnPress
 */
class LearnPress extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'LearnPress';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'LearnPress', 'suretriggers' );
		$this->description = __( 'Easily Create And Sell Online Courses On Your WP Site With LearnPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/learnpress.png';

		parent::__construct();
	}

	/**
	 * Get course context data.
	 *
	 * @param int $course_id course.
	 *
	 * @return array
	 */
	public static function get_lpc_course_context( $course_id ) {
		$context = [];
		$courses = get_post( $course_id );
		if ( empty( $courses ) ) {
			return $context;
		}
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
	public static function get_lpc_lesson_context( $lesson_id ) {
		$context = [];
		$lesson  = get_post( $lesson_id );
		if ( empty( $lesson ) ) {
			return $context;
		}
		$context['lesson']       = $lesson->ID;
		$context['lesson_name']  = $lesson->post_name;
		$context['lesson_title'] = $lesson->post_title;
		$context['lesson_url']   = get_permalink( $lesson_id );
		return $context;
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'LearnPress' );
	}

}

IntegrationsController::register( LearnPress::class );
