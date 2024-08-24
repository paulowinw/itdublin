<?php
/**
 * MarkCourseComplete.
 * php version 5.6
 *
 * @category MarkCourseComplete
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\TutorLMS\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Tutor\Models\CourseModel;

/**
 * MarkCourseComplete
 *
 * @category MarkCourseComplete
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkCourseComplete extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'TutorLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'tlms_mark_course_complete';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark Course Complete', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @return array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$course_id  = isset( $selected_options['courses'] ) ? $selected_options['courses'] : '0';
		$user_email = ( isset( $selected_options['wp_user_email'] ) ) ? $selected_options['wp_user_email'] : '';
		
		if ( ! function_exists( 'tutils' ) || ! class_exists( '\Tutor\Models\CourseModel' ) ) {
			return [];
		}

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id = $user->ID;
				if ( ! tutils()->is_completed_course( $course_id, $user_id ) ) {
					$completion_mode = tutils()->get_option( 'course_completion_process' );
					if ( 'strict' === $completion_mode ) {
						$lesson_query = tutils()->get_lesson( $course_id, - 1 );
						if ( count( $lesson_query ) ) {
							foreach ( $lesson_query as $lesson ) {
								tutils()->mark_lesson_complete( $lesson->ID, $user_id );
							}
						}
					}

					$completed = CourseModel::mark_course_as_completed( $course_id, $user_id );
					if ( $completed ) {
						$context                = WordPress::get_user_context( $user_id );
						$context['course_id']   = $course_id;
						$context['course_name'] = get_the_title( $course_id );
						return $context;
					} else {
						throw new Exception( 'Course Cannot be completed.' );
					}
				} else {
					throw new Exception( 'User has already completed this course.' );
				}
			} else {
				throw new Exception( 'User not found.' );
			}
		} else {
			throw new Exception( 'Enter valid email address.' );
		}
	}
}

MarkCourseComplete::get_instance();
