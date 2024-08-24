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

namespace SureTriggers\Integrations\LearnDash\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LearnDash\LearnDash;
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
	public $integration = 'LearnDash';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'ld_unenroll_from_course';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove user from a course', 'suretriggers' ),
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
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! $user_id ) {
			$this->set_error(
				[
					'msg' => __( 'User Not found', 'suretriggers' ),
				]
			);
			return false;
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
				$this->set_error(
					[
						'msg' => __( 'No course is available ', 'suretriggers' ),
					]
				);
				return false;
			}

			$courses = [ $course_id ];
		}

		$removed_from_courses = [];

		// UnEnroll user in courses.
		$count = 1;
		foreach ( $courses as $course_id ) {
			ld_update_course_access( $user_id, $course_id, true );
			$arr_key                          = count( $courses ) > 1 ? 'course_' . $count : 'course';
			$removed_from_courses[ $arr_key ] = LearnDash::get_course_pluggable_data( $course_id );
			$count++;
		}

		$user_data = LearnDash::get_user_pluggable_data( $user_id );

		return [
			'user'    => $user_data,
			'courses' => $removed_from_courses,
		];
	}

}

RemoveFromCourse::get_instance();
