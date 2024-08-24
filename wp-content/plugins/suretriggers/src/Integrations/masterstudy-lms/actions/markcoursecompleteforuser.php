<?php
/**
 * MarkCourseCompleteForUser.
 * php version 5.6
 *
 * @category MarkCourseCompleteForUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * MarkCourseCompleteForUser
 *
 * @category MarkCourseCompleteForUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkCourseCompleteForUser extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MasterStudyLms';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mark_course_complete';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark a course complete for the user', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$course_id  = $selected_options['course'];
		$user_email = $selected_options['wp_user_email'];

		$user = get_user_by( 'email', $user_email );
		if ( is_object( $user ) ) {
			$user    = get_object_vars( $user );
			$user_id = $user['ID'];
		}

		$curriculum_arr = [];
		// Use curriculum repository class.
		if ( class_exists( '\MasterStudy\Lms\Repositories\CurriculumRepository' ) ) {
			$curriculum_repo = new \MasterStudy\Lms\Repositories\CurriculumRepository();
		} else {
			$curriculum_repo = false;
		}
		if ( $curriculum_repo ) {
			$curriculum = $curriculum_repo->get_curriculum( absint( $course_id ) );
			if ( ! empty( $curriculum ) && is_array( $curriculum ) && isset( $curriculum['materials'] ) ) {
				if ( ! empty( $curriculum['materials'] ) && is_array( $curriculum['materials'] ) ) {
					foreach ( $curriculum['materials'] as $material ) {
						$curriculum_arr[] = [
							'title'     => $material['title'],
							'post_id'   => $material['post_id'],
							'post_type' => $material['post_type'],
						];
					}
				}
			}
		} else {
			// No materials found, try to get them from meta_key curriculum.
			$curriculum = get_post_meta( absint( $course_id ), 'curriculum', true );
			if ( ! empty( $curriculum ) && is_array( $curriculum ) ) {
				if ( class_exists( '\STM_LMS_Helpers' ) ) {
					/** 
					 * Ignoring next line
					 * 
					 * @phpstan-ignore-next-line 
					 * */
					$curriculum       = \STM_LMS_Helpers::only_array_numbers( explode( ',', $curriculum ) );
					$curriculum_posts = get_posts(
						[
							'post__in'       => $curriculum,
							'posts_per_page' => -1,
							'post_type'      => [ 'stm-lessons', 'stm-quizzes' ],
							'post_status'    => 'publish',
						]
					);
				}
				if ( ! empty( $curriculum_posts ) ) {
					foreach ( $curriculum_posts as $material ) {
						$curriculum_arr[] = [
							'title'     => $material->post_title,
							'post_id'   => $material->ID,
							'post_type' => $material->post_type,
						];
					}
				}
			}
		}

		if ( ! empty( $curriculum_arr ) ) {
			// Enroll the user in the course if they are not already enrolled.
			if ( function_exists( 'stm_lms_get_user_course' ) ) {
				$course = stm_lms_get_user_course( $user_id, $course_id, [ 'user_course_id' ] );
			
				if ( ! count( $course ) ) {
					if ( class_exists( '\STM_LMS_Course' ) ) {
						\STM_LMS_Course::add_user_course( $course_id, $user_id, \STM_LMS_Course::item_url( $course_id, '' ), 0 );
						\STM_LMS_Course::add_student( $course_id );
					}
				}
			}
			foreach ( $curriculum_arr as $post ) {
				if ( 'stm-lessons' === $post['post_type'] ) {
					// Complete Lesson.
					$lesson_id = $post['post_id'];
					if ( class_exists( '\STM_LMS_Lesson' ) ) {
						if ( \STM_LMS_Lesson::is_lesson_completed( $user_id, $course_id, $lesson_id ) ) {
							continue;
						}
					}
					$end_time = time();
					/** 
					 * Ignoring next line
					 * 
					 * @phpstan-ignore-next-line 
					 * */
					$start_time = get_user_meta( $user_id, "stm_lms_course_started_{$course_id}_{$lesson_id}", true );

					if ( function_exists( 'stm_lms_add_user_lesson' ) ) {
						stm_lms_add_user_lesson( compact( 'user_id', 'course_id', 'lesson_id', 'start_time', 'end_time' ) );
					}

					if ( class_exists( '\STM_LMS_Course' ) ) {
						\STM_LMS_Course::update_course_progress( $user_id, $course_id );
					}

					do_action( 'stm_lms_lesson_passed', $user_id, $lesson_id );
					/** 
					 * Ignoring next line
					 * 
					 * @phpstan-ignore-next-line 
					 * */
					delete_user_meta( $user_id, "stm_lms_course_started_{$course_id}_{$lesson_id}" );
				}
				if ( 'stm-quizzes' === $post['post_type'] ) {
					// Complete quiz.
					$quiz_id = $post['post_id'];
					if ( class_exists( '\STM_LMS_Quiz' ) ) {
						if ( \STM_LMS_Quiz::quiz_passed( $quiz_id, $user_id ) ) {
							continue;
						}
					}
					$progress  = 100;
					$status    = 'passed';
					$user_quiz = compact( 'user_id', 'course_id', 'quiz_id', 'progress', 'status' );
					if ( function_exists( 'stm_lms_add_user_quiz' ) ) {
						stm_lms_add_user_quiz( $user_quiz );
					}
					if ( function_exists( 'stm_lms_get_delete_user_quiz_time' ) ) {
						stm_lms_get_delete_user_quiz_time( $user_id, $quiz_id );
					}

					if ( class_exists( '\STM_LMS_Course' ) ) {
						\STM_LMS_Course::update_course_progress( $user_id, $course_id );
					}

					$user_quiz['progress'] = round( $user_quiz['progress'], 1 );
					do_action( 'stm_lms_quiz_' . $status, $user_id, $quiz_id, $user_quiz['progress'] );
				}
			}
			$response = [
				'status'   => esc_attr__( 'Success', 'suretriggers' ),
				'response' => esc_attr__( 'Course marked as completed successfully.', 'suretriggers' ),
			];
			return $response;
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Course does not have any lessons or quizzes added.', 'suretriggers' ),
			];

			return $error;
		}

	}
}

MarkCourseCompleteForUser::get_instance();
