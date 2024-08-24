<?php
/**
 * WpcwUserEnrolledInCourse.
 * php version 5.6
 *
 * @category WpcwUserEnrolledInCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPCourseware\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * WpcwUserEnrolledInCourse
 *
 * @category WpcwUserEnrolledInCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WpcwUserEnrolledInCourse {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPCourseware';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wpcw_user_enrolled_in_course';

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 *
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Enrolled In Course', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'wpcw_enroll_user',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 20,
			'accepted_args' => 2,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int   $user_id User ID.
	 * @param array $courses_enrolled Courses Enrolled.
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $courses_enrolled ) {

		if ( empty( $user_id ) ) {
			return;
		}

		foreach ( $courses_enrolled as $course_key ) {
			if ( function_exists( 'WPCW_courses_getCourseDetails' ) ) {
				$course_detail = WPCW_courses_getCourseDetails( $course_key );
				if ( is_object( $course_detail ) ) {
					$course_detail = get_object_vars( $course_detail );
				}
				$context = array_merge( WordPress::get_user_context( $user_id ), $course_detail );
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger'    => $this->trigger,
						'wp_user_id' => $user_id,
						'context'    => $context,
					]
				);
			}
		}
	}
}

WpcwUserEnrolledInCourse::get_instance();
