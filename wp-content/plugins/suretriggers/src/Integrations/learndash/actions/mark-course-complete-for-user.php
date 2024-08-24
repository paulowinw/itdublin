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

namespace SureTriggers\Integrations\LearnDash\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LearnDash\LearnDash;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use WP_Query;

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
	public $integration = 'LearnDash';
	/**
	 * The quiz_list.
	 *
	 * @var array
	 */
	private $quiz_list;
	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'ld_mark_course_complete_for_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark Course Complete For User', 'suretriggers' ),
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
	 * @throws Exception Error.
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! function_exists( 'learndash_process_mark_complete' ) ) {
			return false;
		}
		if ( ! $user_id ) {
			throw new Exception( 'User email not exists.' );
		}

		$course_id = ( isset( $selected_options['sfwd-courses'] ) ) ? $selected_options['sfwd-courses'] : '0';

		if ( 'all' === $course_id ) {

			// Get all courses.
			$query = new WP_Query(
				[
					'post_type'   => 'sfwd-courses',
					'post_status' => 'publish',
					'fields'      => 'ids',
					'nopaging'    => true, //phpcs:ignore
				]
			);

			$courses = $query->get_posts();
		} else {

			$course = get_post( (int) $course_id );
			if ( ! $course ) {
				throw new Exception( 'No course is available.' );
			}

			$courses = [ $course_id ];
		}

		$added_to_courses = [];

		// Enroll user in courses.
		$count = 1;
		foreach ( $courses as $course_id ) {
			self::mark_steps_done( $user_id, $course_id );
			learndash_process_mark_complete( $user_id, $course_id );
			$arr_key                      = count( $courses ) > 1 ? 'course_' . $count : 'course';
			$added_to_courses[ $arr_key ] = LearnDash::get_course_pluggable_data( $course_id );
			$count++;
		}

		$user_data = LearnDash::get_user_pluggable_data( $user_id );

		return [
			'user'    => $user_data,
			'courses' => $added_to_courses,
		];
	}

	/**
	 * Mark steps done 
	 * 
	 * @param int $user_id user id.
	 * @param int $course_id course id.
	 * @return void
	 */
	public function mark_steps_done( $user_id, $course_id ) {
		if ( ! function_exists( 'learndash_get_lesson_list' ) || ! function_exists( 'learndash_process_mark_complete' ) || ! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return;
		}
		$lessons = learndash_get_lesson_list( $course_id, [ 'num' => 0 ] );
		foreach ( $lessons as $lesson ) {
			self::mark_topics_done( $user_id, $lesson->ID, $course_id );
			$lesson_quiz_list = learndash_get_lesson_quiz_list( $lesson->ID, $user_id, $course_id );

			if ( $lesson_quiz_list ) {
				foreach ( $lesson_quiz_list as $ql ) {
					$this->quiz_list[ $ql['post']->ID ] = 0;
				}
			}

			learndash_process_mark_complete( $user_id, $lesson->ID, false, $course_id );
		}

		self::mark_quiz_complete( $user_id, $course_id );
	}

	/**
	 * Marks topics done
	 * 
	 * @param int $user_id User Id.
	 * @param int $lesson_id Lesson ID.
	 * @param int $course_id Course ID.
	 * @return void
	 */
	public function mark_topics_done( $user_id, $lesson_id, $course_id ) {
		if ( ! function_exists( 'learndash_get_topic_list' ) || ! function_exists( 'learndash_process_mark_complete' ) || ! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return;
		}
		$topic_list = learndash_get_topic_list( $lesson_id, $course_id );
		if ( $topic_list ) {
			foreach ( $topic_list as $topic ) {
				learndash_process_mark_complete( $user_id, $topic->ID, false, $course_id );
				$topic_quiz_list = learndash_get_lesson_quiz_list( $topic->ID, $user_id, $course_id );
				if ( $topic_quiz_list ) {
					foreach ( $topic_quiz_list as $ql ) {
						$this->quiz_list[ $ql['post']->ID ] = 0;
					}
				}
			}
		}
	}


	/**
	 * Marks quiz complete
	 * 
	 * @param  int $user_id User ID.
	 * @param int $course_id Course ID.
	 * @return void
	 */
	public function mark_quiz_complete( $user_id, $course_id = null ) {
		if ( ! function_exists( 'learndash_get_course_quiz_list' ) || ! function_exists( 'learndash_is_quiz_complete' ) || ! function_exists( 'learndash_update_user_activity' ) ) { 
			return; 
		}
		$quizzes = learndash_get_course_quiz_list( $course_id, $user_id );
		if ( $quizzes ) {
			
			foreach ( $quizzes as $quiz ) {
				$this->quiz_list[ $quiz['post']->ID ] = 0;
			}
		}
		$quizz_progress = [];
		if ( ! empty( $this->quiz_list ) ) {
			
			$usermeta       = get_user_meta( $user_id, '_sfwd-quizzes', true );
			$quizz_progress = (array) ( empty( $usermeta ) ? [] : $usermeta );

			foreach ( $this->quiz_list as $quiz_id => $quiz ) {
				$quiz_meta = (array) get_post_meta( $quiz_id, '_sfwd-quiz', true );

				if ( learndash_is_quiz_complete( $user_id, $quiz_id, $course_id ) ) {
					continue;
				}

				$quizdata = [
					'quiz'             => $quiz_id,
					'score'            => 0,
					'count'            => 0,
					'pass'             => true,
					'rank'             => '-',
					'time'             => time(),
					'pro_quizid'       => $quiz_meta['sfwd-quiz_quiz_pro'],
					'course'           => $course_id,
					'points'           => 0,
					'total_points'     => 0,
					'percentage'       => 100,
					'timespent'        => 0,
					'has_graded'       => false,
					'statistic_ref_id' => 0,
					'm_edit_by'        => 9999999,  // Manual Edit By ID.
					'm_edit_time'      => time(),
					// Manual Edit timestamp.
				];

				$quizz_progress[] = $quizdata;

				// Then we add the quiz entry to the activity database.
				learndash_update_user_activity(
					[
						'course_id'          => $course_id,
						'user_id'            => $user_id,
						'post_id'            => $quiz_id,
						'activity_type'      => 'quiz',
						'activity_action'    => 'insert',
						'activity_status'    => true,
						'activity_started'   => $quizdata['time'],
						'activity_completed' => $quizdata['time'],
						'activity_meta'      => $quizdata,
					]
				);

			}
		}

		if ( ! empty( $quizz_progress ) ) {
			update_user_meta( $user_id, '_sfwd-quizzes', $quizz_progress );
		}
	}

}

MarkCourseCompleteForUser::get_instance();
