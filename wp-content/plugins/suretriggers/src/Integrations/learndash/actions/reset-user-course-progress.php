<?php
/**
 * ResetUserCourseProgress.
 * php version 5.6
 *
 * @category ResetUserCourseProgress
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
use LDLMS_DB;

/**
 * ResetUserCourseProgress
 *
 * @category ResetUserCourseProgress
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ResetUserCourseProgress extends AutomateAction {


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
	 * The assignment_list.
	 *
	 * @var array
	 */
	private $assignment_list;
	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'ld_reset_user_course_progress';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Reset User Progress', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields template fields.
	 * @param array $selected_options saved template data.
	 * @psalm-suppress UndefinedFunction
	 *
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$course_id = ( isset( $selected_options['sfwd-courses'] ) ) ? $selected_options['sfwd-courses'] : '';
		
		self::delete_user_activity( $user_id, $course_id );
		if ( self::delete_course_progress( $user_id, $course_id ) ) {
			self::reset_quiz_progress( $user_id, $course_id );
			self::delete_assignments();
		}
		
		self::reset_quiz_progress( $user_id, $course_id );

		$user_data = LearnDash::get_user_pluggable_data( $user_id );
		return [
			'user'   => $user_data,
			'course' => LearnDash::get_course_pluggable_data( $course_id ),
		];
	}

	/**
	 *
	 * Delete course related meta keys from user meta table.
	 * Delete all activity related to a course from LD tables
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * 
	 * @return void
	 */
	public function delete_user_activity( $user_id, $course_id ) {
		global $wpdb;
		delete_user_meta( $user_id, 'completed_' . $course_id );
		delete_user_meta( $user_id, 'course_completed_' . $course_id );
		delete_user_meta( $user_id, 'learndash_course_expired_' . $course_id );

		$activity_ids = $wpdb->get_results(
			$wpdb->prepare( 
				"SELECT activity_id FROM {$wpdb->prefix}learndash_user_activity 
            WHERE course_id = %d AND user_id = %d",
				$course_id,
				$user_id 
			) 
		);

		if ( $activity_ids ) {
			foreach ( $activity_ids as $activity_id ) {
				$wpdb->query(
					$wpdb->prepare( 
						"DELETE FROM {$wpdb->prefix}learndash_user_activity_meta 
                    WHERE activity_id = %d",
						$activity_id->activity_id 
					) 
				);
				$wpdb->query(
					$wpdb->prepare( 
						"DELETE FROM {$wpdb->prefix}learndash_user_activity 
                    WHERE activity_id = %d",
						$activity_id->activity_id 
					) 
				);
			}
		}
	}

	/**
	 *
	 * Delete course progress from Usermeta Table
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * 
	 * @return bool
	 */
	public function delete_course_progress( $user_id, $course_id ) {
		$usermeta = get_user_meta( $user_id, '_sfwd-course_progress', true );
		if ( ! empty( $usermeta ) && is_array( $usermeta ) && isset( $usermeta[ $course_id ] ) ) {
			unset( $usermeta[ $course_id ] );
			update_user_meta( $user_id, '_sfwd-course_progress', $usermeta );

			$last_know_step = get_user_meta( $user_id, 'learndash_last_known_page', true );
			if ( is_string( $last_know_step ) ) {
				$last_know_step = explode( ',', $last_know_step );

				if ( isset( $last_know_step[0] ) && isset( $last_know_step[1] ) ) {
					$step_id        = $last_know_step[0];
					$step_course_id = $last_know_step[1];

					if ( (int) $step_course_id === (int) $course_id ) {
						delete_user_meta( $user_id, 'learndash_last_known_page' );
					}
				}
			}

			delete_user_meta( $user_id, 'learndash_last_known_course_' . $course_id );

			return true;
		}

		return false;
	}

	/**
	 *
	 * Delete quiz progress, related to course, quiz etc
	 *
	 * @param int $user_id User ID.
	 * @param int $course_id Course ID.
	 * 
	 * @return void
	 */
	public function reset_quiz_progress( $user_id, $course_id ) {
		if ( ! function_exists( 'learndash_get_lesson_list' ) || 
		! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return;
		}
		$lessons = learndash_get_lesson_list( $course_id, [ 'num' => 0 ] );
		foreach ( $lessons as $lesson ) {
			$this->get_topics_quiz( $user_id, $lesson->ID, $course_id );
			$lesson_quiz_list = learndash_get_lesson_quiz_list( $lesson->ID, $user_id, $course_id );

			if ( $lesson_quiz_list ) {
				foreach ( $lesson_quiz_list as $ql ) {
					$this->quiz_list[ $ql['post']->ID ] = 0;
				}
			}

			$assignments = get_posts(
				[
					'post_type'      => 'sfwd-assignment',
					'posts_per_page' => 10,
					'meta_query'     => [
						'relation' => 'AND',
						[
							'key'     => 'lesson_id',
							'value'   => $lesson->ID,
							'compare' => '=',
						],
						[
							'key'     => 'course_id',
							'value'   => $course_id,
							'compare' => '=',
						],
						[
							'key'     => 'user_id',
							'value'   => $user_id,
							'compare' => '=',
						],
					],
				]
			);

			if ( $assignments ) {
				foreach ( $assignments as $assignment ) {
					$this->assignment_list[] = $assignment->ID;
				}
			}
		}

		$this->delete_quiz_progress( $user_id, $course_id );
	}

	/**
	 *
	 * Get topic quiz + assignment list
	 *
	 * @param int $user_id User ID.
	 * @param int $lesson_id Lesson ID.
	 * @param int $course_id Course ID.
	 * 
	 * @return void
	 */
	public function get_topics_quiz( $user_id, $lesson_id, $course_id ) {
		if ( ! function_exists( 'learndash_get_lesson_quiz_list' ) ||
		! function_exists( 'learndash_get_topic_list' ) ) {
			return;
		}
		$topic_list = learndash_get_topic_list( $lesson_id, $course_id );
		if ( $topic_list ) {
			foreach ( $topic_list as $topic ) {
				$topic_quiz_list = learndash_get_lesson_quiz_list( $topic->ID, $user_id, $course_id );
				if ( $topic_quiz_list ) {
					foreach ( $topic_quiz_list as $ql ) {
						$this->quiz_list[ $ql['post']->ID ] = 0;
					}
				}

				$assignments = get_posts(
					[
						'post_type'      => 'sfwd-assignment',
						'posts_per_page' => 10,
						'meta_query'     => [
							'relation' => 'AND',
							[
								'key'     => 'lesson_id',
								'value'   => $topic->ID,
								'compare' => '=',
							],
							[
								'key'     => 'course_id',
								'value'   => $course_id,
								'compare' => '=',
							],
							[
								'key'     => 'user_id',
								'value'   => $user_id,
								'compare' => '=',
							],
						],
					]
				);

				if ( $assignments ) {
					foreach ( $assignments as $assignment ) {
						$this->assignment_list[] = $assignment->ID;
					}
				}
			}
		}
	}

	/**
	 *
	 * Actually deleting quiz data from user meta and pro quiz activity table
	 *
	 * @param  int $user_id User ID.
	 * @param int $course_id Course ID.
	 * 
	 * @return void
	 */
	public function delete_quiz_progress( $user_id, $course_id = null ) {
		if ( ! function_exists( 'learndash_get_course_quiz_list' ) || ! class_exists( 'LDLMS_DB' ) ) {
			return;
		}
		$quizzes = learndash_get_course_quiz_list( $course_id, $user_id );
		if ( $quizzes ) {
			foreach ( $quizzes as $quiz ) {
				$this->quiz_list[ $quiz['post']->ID ] = 0;
			}
		}
		global $wpdb;

		$quizz_progress = [];
		if ( ! empty( $this->quiz_list ) ) {
			$usermeta       = get_user_meta( $user_id, '_sfwd-quizzes', true );
			$quizz_progress = empty( $usermeta ) ? [] : $usermeta;
			if ( is_array( $quizz_progress ) ) {
				foreach ( $quizz_progress as $k => $p ) {
					if ( is_array( $p ) && key_exists( $p['quiz'], $this->quiz_list ) && $p['course'] == $course_id ) {
						$statistic_ref_id = $p['statistic_ref_id'];
						unset( $quizz_progress[ $k ] );
						if ( ! empty( $statistic_ref_id ) ) {

							if ( class_exists( '\LDLMS_DB' ) ) {
								$pro_quiz_stat_table     = LDLMS_DB::get_table_name( 'quiz_statistic' );
								$pro_quiz_stat_ref_table = LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
							} else {
								$pro_quiz_stat_table     = $wpdb->prefix . 'wp_pro_quiz_statistic';
								$pro_quiz_stat_ref_table = $wpdb->prefix . 'wp_pro_quiz_statistic_ref';
							}

							$wpdb->query(
								$wpdb->prepare(
									'DELETE FROM %s WHERE statistic_ref_id = %d',
									$pro_quiz_stat_table,
									$statistic_ref_id 
								) 
							);
							$wpdb->query(
								$wpdb->prepare(
									'DELETE FROM %s WHERE statistic_ref_id = %d',
									$pro_quiz_stat_ref_table,
									$statistic_ref_id 
								) 
							);
						}
					}
				}
			}
		}

		update_user_meta( $user_id, '_sfwd-quizzes', $quizz_progress );
		$usermeta       = get_user_meta( $user_id, '_sfwd-quizzes', true );
		$quizz_progress = empty( $usermeta ) ? [] : $usermeta;
		if ( is_array( $quizz_progress ) ) {
			foreach ( $quizz_progress as $k => $p ) {
				if ( is_array( $p ) && $p['course'] == $course_id ) {
					$statistic_ref_id = $p['statistic_ref_id'];
					unset( $quizz_progress[ $k ] );
					if ( ! empty( $statistic_ref_id ) ) {

						if ( class_exists( '\LDLMS_DB' ) ) {
							$pro_quiz_stat_table     = LDLMS_DB::get_table_name( 'quiz_statistic' );
							$pro_quiz_stat_ref_table = LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
						} else {
							$pro_quiz_stat_table     = $wpdb->prefix . 'wp_pro_quiz_statistic';
							$pro_quiz_stat_ref_table = $wpdb->prefix . 'wp_pro_quiz_statistic_ref';
						}

						$wpdb->query(
							$wpdb->prepare(
								'DELETE FROM %s WHERE statistic_ref_id = %d',
								$pro_quiz_stat_table,
								$statistic_ref_id 
							) 
						);
						$wpdb->query(
							$wpdb->prepare(
								'DELETE FROM %s WHERE statistic_ref_id = %d',
								$pro_quiz_stat_ref_table,
								$statistic_ref_id 
							) 
						);
					}
				}
			}
		}
		update_user_meta( $user_id, '_sfwd-quizzes', $quizz_progress );
	}

	/**
	 * Delete assignments of course, related to lessons / topics
	 * 
	 * @return void
	 */
	public function delete_assignments() {
		global $wpdb;
		$assignments = $this->assignment_list;
		if ( $assignments ) {
			foreach ( $assignments as $assignment ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE ID = %d", $assignment ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d", $assignment ) );
			}
		}
	}

}

ResetUserCourseProgress::get_instance();
