<?php
/**
 * MarkLessonCompleteForUser.
 * php version 5.6
 *
 * @category MarkLessonCompleteForUser
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
 * MarkLessonCompleteForUser
 *
 * @category MarkLessonCompleteForUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkLessonCompleteForUser extends AutomateAction {

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
	public $action = 'ld_mark_lesson_complete_for_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark Lesson Complete For User', 'suretriggers' ),
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

		$course_id = $selected_options['sfwd-courses'];
		$lesson_id = $selected_options['sfwd-lessons'];
		self::mark_steps_done( $user_id, $lesson_id, $course_id );

		$user_data   = LearnDash::get_user_pluggable_data( $user_id );
		$lesson      = get_post( (int) $lesson_id, ARRAY_A );
		$lesson_data = [];
		if ( is_array( $lesson ) ) {
			$lesson_data = [
				'ID'                 => $lesson['ID'],
				'title'              => $lesson['post_title'],
				'URL'                => get_permalink( $lesson['ID'] ),
				'status'             => $lesson['post_status'],
				'featured_image_id'  => get_post_meta( $lesson['ID'], '_thumbnail_id', true ),
				'featured_image_url' => get_the_post_thumbnail_url( $lesson['ID'] ),
			];
		}
		return [
			'user'   => $user_data,
			'course' => LearnDash::get_course_pluggable_data( $course_id ),
			'lesson' => $lesson_data,
		];
	}

	/**
	 * Mark steps done 
	 * 
	 * @param int $user_id user id.
	 * @param int $lesson_id lesson id.
	 * @param int $course_id course id.
	 * @return void
	 */
	public function mark_steps_done( $user_id, $lesson_id, $course_id ) {
		if ( ! function_exists( 'learndash_get_lesson_list' ) || 
		! function_exists( 'learndash_process_mark_complete' ) || 
		! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return;
		}

		self::mark_topics_done( $user_id, $lesson_id, $course_id );
		$lesson_quiz_list = learndash_get_lesson_quiz_list( $lesson_id, $user_id, $course_id );
		
		if ( ! empty( $lesson_quiz_list ) ) {
			foreach ( $lesson_quiz_list as $ql ) {
				$this->quiz_list[ $ql['post']->ID ] = 0;
			}
			self::mark_quiz_complete( $user_id, $course_id );
		}

		learndash_process_mark_complete( $user_id, $lesson_id, false, $course_id );
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
		if ( ! function_exists( 'learndash_get_topic_list' ) || 
		! function_exists( 'learndash_process_mark_complete' ) || 
		! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return;
		}
		$topic_list = learndash_get_topic_list( $lesson_id, $course_id );
		if ( ! empty( $topic_list ) ) {
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

MarkLessonCompleteForUser::get_instance();
