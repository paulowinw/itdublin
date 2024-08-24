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

namespace SureTriggers\Integrations\LifterLMS\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Traits\SingletonLoader;

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
	public $integration = 'LifterLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lms_enroll_to_course';

	use SingletonLoader;


	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Enroll User in a course', 'suretriggers' ),
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
	 * @return void|bool
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		/**
		 * Int Course ID
		 *
		 * @var int|mixed|null $course_id Course ID.
		 */
		$course_id = isset( $selected_options['course'] ) ? $selected_options['course'] : '0';

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

		// Enroll user in courses.

		llms_enroll_student( $user_id, $course_id ); // @psalm-suppress UndefinedMethod

		$course_data = LifterLMS::get_lms_course_context( $course_id );

		return $course_data;
	}

}

EnrollToCourse::get_instance();
