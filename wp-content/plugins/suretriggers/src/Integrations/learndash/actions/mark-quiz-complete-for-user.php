<?php
/**
 * MarkQuizCompleteForUser.
 * php version 5.6
 *
 * @category MarkQuizCompleteForUser
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
 * MarkQuizCompleteForUser
 *
 * @category MarkQuizCompleteForUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkQuizCompleteForUser extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LearnDash';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'ld_mark_quiz_complete_for_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark Quiz Complete For User', 'suretriggers' ),
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
		! function_exists( 'learndash_user_get_course_progress' ) ||
		! function_exists( 'learndash_process_user_course_progress_update' ) ) {
			return false;
		}
		if ( ! $user_id ) {
			throw new Exception( 'User email not exists.' );
		}

		$course_id = ( isset( $selected_options['sfwd-courses'] ) ) ? $selected_options['sfwd-courses'] : '0';
		$lesson_id = ( isset( $selected_options['sfwd_lessons_topics'] ) ) ? $selected_options['sfwd_lessons_topics'] : '0';
		$quiz_id   = ( isset( $selected_options['sfwd-quiz'] ) ) ? $selected_options['sfwd-quiz'] : '0';

		$course_progress = self::get_user_current_course_progress( $user_id, $course_id );
		$course_updates  = self::course_progress_updates( $course_progress, $quiz_id, $lesson_id );

		// If the course progress is empty, then there is nothing to update.
		if ( empty( $course_updates ) ) {
			throw new Exception( 'Lesson/Topic not associated with quiz.' );
		}

		$update = [
			'course' => [
				$course_id => $course_updates,
			],
			'quiz'   => [
				$course_id => [
					$quiz_id => 1,
				],
			],
		];

		// Update the user progress.
		$updated_course_ids = learndash_process_user_course_progress_update( $user_id, $update );

		$user_data   = LearnDash::get_user_pluggable_data( $user_id );
		$lesson      = get_post( (int) $lesson_id, ARRAY_A );
		$lesson_data = [];
		$quiz_data   = [];
		if ( is_array( $lesson ) ) {
			$lesson_data = [
				'ID'    => $lesson['ID'],
				'title' => $lesson['post_title'],
				'URL'   => get_permalink( $lesson['ID'] ),
			];
		}
		$quiz = get_post( (int) $quiz_id, ARRAY_A );
		if ( is_array( $quiz ) ) {
			$quiz_data = [
				'ID'    => $quiz['ID'],
				'title' => $quiz['post_title'],
				'URL'   => get_permalink( $quiz['ID'] ),
			];
		}
		return [
			'user'   => $user_data,
			'course' => LearnDash::get_course_pluggable_data( $course_id ),
			'lesson' => $lesson_data,
			'quiz'   => $quiz_data,
		];
	}

	/**
	 * Get current course progress for a user.
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return array
	 */
	public function get_user_current_course_progress( $user_id, $course_id ) {

		if ( ! function_exists( 'learndash_course_status' ) ||
		! function_exists( 'learndash_get_course_quiz_list' ) ||
		! function_exists( 'learndash_is_quiz_complete' ) ||
		! function_exists( 'learndash_get_course_lessons_list' ) ||
		! function_exists( 'learndash_topic_dots' ) ||
		! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return [];
		}

		$status   = learndash_course_status( $course_id, $user_id );
		$progress = [
			'is_completed' => 'completed' === $status ? 1 : 0,
			'lessons'      => [],
			'quiz'         => [],
			'course'       => [
				'lessons' => [],
				'topics'  => [],
			],
		];

		// Get all quizzes for the course.
		$quizzes = learndash_get_course_quiz_list( $course_id );
		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $key => $quiz ) {
				$quiz_id                      = $quiz['post']->ID;
				$quiz_completed               = learndash_is_quiz_complete( $user_id, $quiz_id, $course_id );
				$progress['quiz'][ $quiz_id ] = $quiz_completed ? 1 : 0;
			}
		}

		// Get all lessons for the course.
		$lessons = learndash_get_course_lessons_list( $course_id, $user_id, [ 'per_page' => 9999 ] );
		if ( ! empty( $lessons ) ) {
			foreach ( $lessons as $lesson ) {
				$lesson_id                                   = $lesson['post']->ID;
				$lesson_completed                            = 'completed' === $lesson['status'] ? 1 : 0;
				$progress['course']['lessons'][ $lesson_id ] = $lesson_completed;
				// Build Lesson Progress.
				$progress['lessons'][ $lesson_id ]                 = ! empty( $progress['lessons'][ $lesson_id ] ) ? $progress['lessons'][ $lesson_id ] : [];
				$progress['lessons'][ $lesson_id ]['is_completed'] = $lesson_completed;
				$progress['lessons'][ $lesson_id ]['topics']       = ! empty( $progress['lessons'][ $lesson_id ]['topics'] ) ? $progress['lessons'][ $lesson_id ]['topics'] : [];
				$progress['lessons'][ $lesson_id ]['quizzes']      = ! empty( $progress['lessons'][ $lesson_id ]['quizzes'] ) ? $progress['lessons'][ $lesson_id ]['quizzes'] : [];

				// Get all topics for the lesson.
				$topics = learndash_topic_dots( $lesson_id, false, 'array', $user_id, $course_id );
				if ( ! empty( $topics ) ) {
					$progress['course']['topics'][ $lesson_id ] = ! empty( $progress['course']['topics'][ $lesson_id ] ) ? $progress['course']['topics'][ $lesson_id ] : [];
					foreach ( $topics as $topic ) {
						$topic_id        = $topic->ID;
						$topic_completed = ! empty( $topic->completed ) ? 1 : 0;

						// Build Topic Progress.
						$progress['lessons'][ $lesson_id ]['topics'][ $topic_id ]                 = ! empty( $progress['lessons'][ $lesson_id ]['topics'][ $topic_id ] ) ? $progress['lessons'][ $lesson_id ]['topics'][ $topic_id ] : [];
						$progress['lessons'][ $lesson_id ]['topics'][ $topic_id ]['is_completed'] = $topic_completed;
						$progress['course']['topics'][ $lesson_id ][ $topic_id ]                  = $topic_completed;
						$progress['lessons'][ $lesson_id ]['topics'][ $topic_id ]['quizzes']      = ! empty( $progress['lessons'][ $lesson_id ]['topics'][ $topic_id ]['quizzes'] ) ? $progress['lessons'][ $lesson_id ]['topics'][ $topic_id ]['quizzes'] : [];

						// Get all quizzes for the topic.
						$topic_quizzes = learndash_get_lesson_quiz_list( $topic_id, null, $course_id );
						if ( ! empty( $topic_quizzes ) ) {
							foreach ( $topic_quizzes as $key => $quiz ) {
								$quiz_id        = $quiz['post']->ID;
								$quiz_completed = learndash_is_quiz_complete( $user_id, $quiz_id, $course_id ) ? 1 : 0;
								$progress['lessons'][ $lesson_id ]['topics'][ $topic_id ]['quizzes'][ $quiz_id ] = $quiz_completed;
								$progress['quiz'][ $quiz_id ] = $quiz_completed;
							}
						}
					}
				}

				// Lesson Quizzes.
				$lesson_quizzes = learndash_get_lesson_quiz_list( $lesson_id, $user_id, $course_id );
				if ( ! empty( $lesson_quizzes ) ) {
					foreach ( $lesson_quizzes as $key => $quiz ) {
						$quiz_id        = $quiz['post']->ID;
						$quiz_completed = learndash_is_quiz_complete( $user_id, $quiz_id, $course_id ) ? 1 : 0;
						$progress['lessons'][ $lesson_id ]['quizzes'][ $quiz_id ] = $quiz_completed;
						$progress['quiz'][ $quiz_id ]                             = $quiz_completed;
					}
				}
			}
		}

		return $progress;
	}

	/**
	 * Update course progress array.
	 *
	 * @param array $course_progress Course Progress.
	 * @param int   $quiz_id Quiz ID.
	 * @param int   $step_id Step ID.
	 *
	 * @return array
	 */
	public function course_progress_updates( $course_progress, $quiz_id, $step_id ) {

		$updates = $course_progress['course'];
		$lessons = $course_progress['lessons'];

		$quiz_lesson_id = 0;
		$quiz_topic_id  = 0;
		foreach ( $lessons as $lesson_id => $lesson ) {
			if ( ! empty( $lesson['quizzes'] ) ) {
				if ( array_key_exists( $quiz_id, $lesson['quizzes'] ) ) {
					$quiz_lesson_id = $lesson_id;
				}
			}
			if ( ! empty( $lesson['topics'] ) ) {
				foreach ( $lesson['topics'] as $topic_id => $topic ) {
					if ( ! empty( $topic['quizzes'] ) ) {
						if ( array_key_exists( $quiz_id, $topic['quizzes'] ) ) {
							$quiz_lesson_id = $lesson_id;
							$quiz_topic_id  = $topic_id;
						}
					}
				}
			}
		}

		$quiz_step_ids = [
			'lesson' => $quiz_lesson_id,
			'topic'  => $quiz_topic_id,
		];
		$lesson_id     = $quiz_step_ids['lesson'];
		$topic_id      = $quiz_step_ids['topic'];

		if ( $step_id > 0 ) {
			if ( ! in_array( $step_id, $quiz_step_ids, true ) ) {
				return [];
			}
		}

		if ( ! empty( $topic_id ) ) {
			$lessons[ $lesson_id ]['topics'][ $topic_id ]['quizzes'][ $quiz_id ] = 1;
			if ( self::all_step_quizzes_completed( $lessons[ $lesson_id ]['topics'][ $topic_id ] ) ) {
				$updates['topics'][ $lesson_id ][ $topic_id ]                 = 1;
				$lessons[ $lesson_id ]['topics'][ $topic_id ]['is_completed'] = 1;
			}
		}

		if ( self::all_lesson_steps_completed( $lessons[ $lesson_id ], $quiz_id, $topic_id ) ) {
			$updates['lessons'][ $lesson_id ] = 1;
		}

		return $updates;
	}

	/**
	 * Check if all quizzes in a step are completed.
	 *
	 * @param array $step Step.
	 * @param int   $complete_quiz_id Complete Quiz ID.
	 *
	 * @return bool
	 */
	public function all_step_quizzes_completed( $step, $complete_quiz_id = 0 ) {
		foreach ( $step['quizzes'] as $quiz_id => $status ) {
			if ( empty( $status ) ) {
				if ( ! empty( $complete_quiz_id ) ) {
					if ( $quiz_id !== $complete_quiz_id ) {
						return false;
					}
				} else {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Check if all lesson steps are completed.
	 *
	 * @param array $lesson Lesson.
	 * @param int   $quiz_id Quiz ID.
	 * @param int   $quiz_topic_id Quiz Post ID.
	 *
	 * @return bool
	 */
	public function all_lesson_steps_completed( $lesson, $quiz_id, $quiz_topic_id ) {

		if ( ! empty( $lesson['topics'] ) ) {
			foreach ( $lesson['topics'] as $topic_id => $topic ) {
				if ( ! self::all_step_quizzes_completed( $topic, $quiz_id ) ) {
					return false;
				}
			}

			foreach ( $lesson['topics'] as $topic_id => $topic ) {
				if ( empty( $topic['is_completed'] ) && $topic_id !== $quiz_topic_id ) {
					return false;
				}
			}
		}

		if ( ! empty( $lesson['quizzes'] ) ) {
			if ( ! self::all_step_quizzes_completed( $lesson, $quiz_id ) ) {
				return false;
			}
		}

		return true;
	}

}

MarkQuizCompleteForUser::get_instance();
