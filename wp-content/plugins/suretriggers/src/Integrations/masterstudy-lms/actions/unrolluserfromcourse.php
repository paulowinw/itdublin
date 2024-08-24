<?php
/**
 * UnrollUserFromCourse.
 * php version 5.6
 *
 * @category UnrollUserFromCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * UnrollUserFromCourse
 *
 * @category UnrollUserFromCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UnrollUserFromCourse extends AutomateAction {

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
	public $action = 'unroll_user_from_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Unroll User From Course', 'suretriggers' ),
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
		$course_id = $selected_options['course'];
		$user_id   = $selected_options['wp_user_email'];

		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );

			if ( $user ) {
				$user_id           = $user->ID;
				$fields['user_id'] = $user_id;
			} else {
				// If there's no user found, return default message.
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'User with the email provided not found.', 'suretriggers' ),
				];
				return $error;
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}

		// UnEnroll the user in the course if they are already enrolled.
		if ( function_exists( 'stm_lms_get_user_course' ) ) {
			$course = stm_lms_get_user_course( $user_id, $course_id, [ 'user_course_id' ] );
			if ( empty( $course ) ) {
				$response = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'User not enrolled into this course.', 'suretriggers' ),
				];
			} else {
				// Reset the user's progress.
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
					if ( ! empty( $curriculum ) ) {
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
					foreach ( $curriculum_arr as $post_object ) {

						if ( 'stm-lessons' === $post_object['post_type'] ) {
							// Reset lesson progress.
							if ( function_exists( 'stm_lms_delete_user_lesson' ) ) {
								stm_lms_delete_user_lesson( $user_id, $course_id, $post_object['post_id'] );
							}
						}

						if ( 'stm-assignments' === $post_object['post_type'] ) {
							// Reset assignment progress.
							$args = [
								'posts_per_page' => - 1,
								'post_type'      => 'stm-user-assignment',
								'post_status'    => [
									'pending',
									'publish',
									'draft',
								],
								'meta_query'     => [
									'relation' => 'AND',
									[
										'key'     => 'course_id',
										'value'   => $course_id,
										'compare' => '=',
									],
									[
										'key'     => 'assignment_id',
										'value'   => $post_object['post_id'],
										'compare' => '=',
									],
									[
										'key'     => 'student_id',
										'value'   => $user_id,
										'compare' => '=',
									],
								],
							];
							$q    = new WP_Query( $args );
							if ( $q->have_posts() ) {
								while ( $q->have_posts() ) {
									$q->the_post();
									wp_delete_post( intval( get_the_ID() ) );
								}
							}
						}

						if ( 'stm-quizzes' === $post_object['post_type'] ) {
							// Reset quiz progress.
							if ( function_exists( 'stm_lms_delete_user_quiz' ) ) {
								stm_lms_delete_user_quiz( $user_id, $course_id, $post_object['post_id'] );
							}
						}

						// Reset the user answers.
						if ( function_exists( 'stm_lms_reset_user_answers' ) ) {
							stm_lms_reset_user_answers( $course_id, $user_id );
						}

						// Update course progress.
						if ( class_exists( '\STM_LMS_Course' ) ) {
							\STM_LMS_Course::update_course_progress( $user_id, $course_id );
						}
					}

					// Set the success response.
					$response = [
						'status'   => esc_attr__( 'Success', 'suretriggers' ),
						'response' => esc_attr__( 'User progress reset successfully.', 'suretriggers' ),
					];
				} else {
					// If there's no response, return an error message.
					$error =
						[
							'status'   => esc_attr__( 'Error', 'suretriggers' ),
							'response' => esc_attr__( 'Something went wrong. Please check the action step configuration.', 'suretriggers' ),
						];
					return $error;
				}
	
				// Unroll the user from the course.
				if ( function_exists( 'stm_lms_get_delete_user_course' ) ) {
					stm_lms_get_delete_user_course( $user_id, $course_id );
				}
				if ( class_exists( '\STM_LMS_Course' ) ) {
					\STM_LMS_Course::remove_student( $course_id );
				}
	
				$response = [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'User unrolled from course successfully.', 'suretriggers' ),
				];
			}
	
			return $response;
		}
	}
}

UnrollUserFromCourse::get_instance();
