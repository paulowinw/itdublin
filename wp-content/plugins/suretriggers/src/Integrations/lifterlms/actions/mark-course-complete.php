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

namespace SureTriggers\Integrations\LifterLMS\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use LLMS_Course;
use LLMS_Section;

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
	public $integration = 'LifterLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lms_mark_course_complete';

	use SingletonLoader;


	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark course complete for User', 'suretriggers' ),
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
	 * @psalm-suppress UndefinedMethod
	 *
	 * @return void|bool|array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$course_id = isset( $selected_options['course'] ) ? $selected_options['course'] : '0';
		$user_id   = $selected_options['wp_user_email'];

		if ( ! class_exists( 'LLMS_Course' ) || ! class_exists( 'LLMS_Section' ) ) {
			return;
		}

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );

			if ( $user ) {
				$user_id = $user->ID;
				$course  = get_post( (int) $course_id );
				if ( ! $course ) {
					$this->set_error(
						[
							'msg' => __( 'No course is available ', 'suretriggers' ),
						]
					);
					return false;
				}

				if ( ! function_exists( 'llms_mark_complete' ) ) {
					$this->set_error(
						[
							'msg' => __( 'The function llms_mark_complete does not exist', 'suretriggers' ),
						]
					);
					return false;
				}
				
				$course   = new \LLMS_Course( $course_id );
				$sections = $course->get_sections();
				if ( ! empty( $sections ) ) {
					foreach ( $sections as $section ) {
						$section = new \LLMS_Section( $section->id );
						$lessons = $section->get_lessons();
						if ( ! empty( $lessons ) ) {
							foreach ( $lessons as $lesson ) {
								llms_mark_complete( $user_id, $lesson->id, 'lesson' );
							}
						}
						llms_mark_complete( $user_id, $section->id, 'section' );
					}
				}
				llms_mark_complete( $user_id, $course_id, 'course' );
				return LifterLMS::get_lms_course_context( $course_id );
			} else {
				throw new Exception( 'User not exists.' );
			}
		} else {
			throw new Exception( 'Enter valid email address.' );
		}
	}

}

MarkCourseComplete::get_instance();
