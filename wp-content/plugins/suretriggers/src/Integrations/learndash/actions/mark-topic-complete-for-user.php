<?php
/**
 * MarkTopicCompleteForUser.
 * php version 5.6
 *
 * @category MarkTopicCompleteForUser
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

/**
 * MarkTopicCompleteForUser
 *
 * @category MarkTopicCompleteForUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkTopicCompleteForUser extends AutomateAction {

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
	public $action = 'ld_mark_topic_complete_for_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark Topic Complete For User', 'suretriggers' ),
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
		if ( ! function_exists( 'learndash_process_mark_complete' ) ||
		! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return false;
		}
		if ( ! $user_id ) {
			throw new Exception( 'User email not exists.' );
		}

		$course_id = ( isset( $selected_options['sfwd-courses'] ) ) ? $selected_options['sfwd-courses'] : '0';
		$lesson_id = ( isset( $selected_options['sfwd-lessons'] ) ) ? $selected_options['sfwd-lessons'] : '0';
		$topic_id  = ( isset( $selected_options['sfwd-topics'] ) ) ? $selected_options['sfwd-topics'] : '0';

		$topic_quiz_list = learndash_get_lesson_quiz_list( $topic_id, $user_id, $course_id );
		
		if ( ! empty( $topic_quiz_list ) ) {
			foreach ( $topic_quiz_list as $ql ) {
				$this->quiz_list[ $ql['post']->ID ] = 0;
			}
			self::mark_quiz_complete( $user_id, $course_id );
		}
		learndash_process_mark_complete( $user_id, $topic_id, false, $course_id );
		

		$user_data   = LearnDash::get_user_pluggable_data( $user_id );
		$lesson      = get_post( (int) $lesson_id, ARRAY_A );
		$lesson_data = [];
		$topic_data  = [];
		if ( is_array( $lesson ) ) {
			$lesson_data = [
				'ID'    => $lesson['ID'],
				'title' => $lesson['post_title'],
				'URL'   => get_permalink( $lesson['ID'] ),
			];
		}
		$topic = get_post( (int) $topic_id, ARRAY_A );
		if ( is_array( $topic ) ) {
			$topic_data = [
				'ID'    => $topic['ID'],
				'title' => $topic['post_title'],
				'URL'   => get_permalink( $topic['ID'] ),
			];
		}
		return [
			'user'   => $user_data,
			'course' => LearnDash::get_course_pluggable_data( $course_id ),
			'lesson' => $lesson_data,
			'topic'  => $topic_data,
		];
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
					'm_edit_by'        => 9999999,
					'm_edit_time'      => time(),
				];

				$quizz_progress[] = $quizdata;
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

MarkTopicCompleteForUser::get_instance();
