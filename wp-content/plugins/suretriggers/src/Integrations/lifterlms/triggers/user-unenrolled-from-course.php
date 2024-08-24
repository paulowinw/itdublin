<?php
/**
 * UserUnenrolledFromCourse.
 * php version 5.6
 *
 * @category UserUnenrolledFromCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LifterLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * UserUnenrolledFromCourse
 *
 * @category UserUnenrolledFromCourse
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserUnenrolledFromCourse {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LifterLMS';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'lifterlms_user_unenrolled_from_course';

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Enrolled In Course', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'llms_user_removed_from_course',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 20,
			'accepted_args' => 4,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param int    $student_id user id.
	 * @param int    $course_id Course id.
	 * @param array  $trigger Trigger.
	 * @param string $status Status.
	 *
	 * @return void
	 */
	public function trigger_listener( $student_id, $course_id, $trigger, $status ) {

		if ( empty( $course_id ) || 'cancelled' != $status ) {
			return;
		}

		$context = array_merge(
			WordPress::get_user_context( $student_id ),
			LifterLMS::get_lms_course_context( $course_id )
		);

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger'    => $this->trigger,
				'wp_user_id' => $student_id,
				'context'    => $context,
			]
		);
	}

}

UserUnenrolledFromCourse::get_instance();
