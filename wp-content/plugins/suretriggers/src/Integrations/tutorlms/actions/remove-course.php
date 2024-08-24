<?php
/**
 * RemoveFromCourse.
 * php version 5.6
 *
 * @category RemoveFromCourse
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
use WP_Query;

/**
 * RemoveFromCourse
 *
 * @category RemoveFromCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveFromCourse extends AutomateAction {

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
	public $action = 'tlms_remove_from_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove User from Course', 'suretriggers' ),
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
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$context = [];

		$remove_from_all = ( isset( $selected_options['courses'] ) && 'all' === $selected_options['courses'] ) ? true : false;
		$course_id       = ( isset( $selected_options['courses'] ) && 'all' !== $selected_options['courses'] ) ? $selected_options['courses'] : 0;
		$course_data     = get_post( $course_id );
		$user_email      = ( isset( $selected_options['wp_user_email'] ) ) ? $selected_options['wp_user_email'] : '';
		$user_id         = email_exists( $user_email );

		$enrolled_courses    = tutor_utils()->get_enrolled_courses_by_user( $user_id );
		$enrolled_courses_id = [];

		if ( false === $enrolled_courses ) {
			throw new Exception( $user_email . ' is not enrolled in any course.' );
		}

		foreach ( $enrolled_courses->posts as $key => $course ) {
			$enrolled_courses_id[] = $course->ID;
		}

		if ( ! $remove_from_all && ! in_array( $course_id, $enrolled_courses_id, true ) ) {
			throw new Exception( $user_email . ' is not enrolled in ' . $course_data->post_title . ' course.' );
		}
		$user                  = get_user_by( 'id', $user_id );
		$context['user_id']    = $user->ID;
		$context['user_name']  = $user->display_name;
		$context['user_email'] = $user->user_email;
		if ( $remove_from_all ) {
			$query   = new WP_Query(
				[
					'post_type'   => tutor()->course_post_type,
					'post_status' => 'publish',
					'fields'      => 'ids',
					'nopaging'    => true, //phpcs:ignore
				]
			);
			$courses = $query->get_posts();
		} else {
			$course = get_post( (int) $course_id );
			if ( ! $course ) {
				throw new Exception( 'No Course is available.' );
			}
			$courses = [ $course_id ];
		}

		if ( empty( $courses ) ) {
			throw new Exception( 'No Courses are available.' );
		}

		$unenrolled_courses = [];
		foreach ( $courses as $course_id ) {
			if ( in_array( $course_id, $enrolled_courses_id, true ) ) {
				$course_data                  = get_post( $course_id );
				$unenrolled_courses['id'][]   = $course_data->ID;
				$unenrolled_courses['name'][] = $course_data->post_title;
				tutor_utils()->cancel_course_enrol( $course_id, $user_id );
			}
		}
		$context['course_id']   = implode( ',', $unenrolled_courses['id'] );
		$context['course_name'] = implode( ',', $unenrolled_courses['name'] );
		return $context;
	}
}

RemoveFromCourse::get_instance();
