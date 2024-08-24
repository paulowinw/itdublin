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

namespace SureTriggers\Integrations\LifterLMS\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Traits\SingletonLoader;

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
	public $integration = 'LifterLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lms_remove_from_course';

	use SingletonLoader;


	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove user from course', 'suretriggers' ),
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
	 *
	 * @psalm-suppress InvalidScalarArgument
	 * @psalm-suppress UndefinedMethod
	 *
	 * @return bool|array|object
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		/**
		 * Int Course ID
		 *
		 * @var int|string|null $course_id Course ID.
		 */
		$course_id = isset( $selected_options['course'] ) ? $selected_options['course'] : '0';
		$course    = get_post( (int) $course_id );

		if ( ! $course ) {
			$this->set_error(
				[
					'msg' => __( 'No course is available ', 'suretriggers' ),
				]
			);
			return false;
		}

		llms_unenroll_student( $user_id, $course_id );
		$course_data = LifterLMS::get_lms_course_context( $course_id );

		return $course_data;
	}
}

RemoveFromCourse::get_instance();
