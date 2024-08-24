<?php
/**
 * EnrollToCourse.
 * php version 5.6
 *
 * @category EnrollToCourse
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
 * EnrollToCourse
 *
 * @category EnrollToCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class EnrollToCourse extends AutomateAction {

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
	public $action = 'tlms_enroll_to_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Enroll User in Course', 'suretriggers' ),
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

		$course_id  = isset( $selected_options['courses'] ) ? $selected_options['courses'] : '0';
		$user_email = ( isset( $selected_options['wp_user_email'] ) ) ? $selected_options['wp_user_email'] : '';
		$user_id    = email_exists( $user_email );

		$user                  = get_user_by( 'id', $user_id );
		$context['user_id']    = $user->ID;
		$context['user_name']  = $user->display_name;
		$context['user_email'] = $user->user_email;
		if ( 'all' === $course_id ) {
			// Get all courses.
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
		$enrolled_courses = [];

		foreach ( $courses as $course_id ) {
			$course_data                = get_post( $course_id );
			$enrolled_courses['id'][]   = $course_data->ID;
			$enrolled_courses['name'][] = $course_data->post_title;
			// Filter purchaseability to always return false when enrolling through this action.
			add_filter( 'is_course_purchasable', '__return_false', 10 );

			tutor_utils()->do_enroll( $course_id, false, $user_id );

			// Remove the filter so standard enrollments can still continue.
			remove_filter( 'is_course_purchasable', '__return_false' );
		}
		$context['course_id']   = implode( ',', $enrolled_courses['id'] );
		$context['course_name'] = implode( ',', $enrolled_courses['name'] );
		return $context;
	}
}

EnrollToCourse::get_instance();
