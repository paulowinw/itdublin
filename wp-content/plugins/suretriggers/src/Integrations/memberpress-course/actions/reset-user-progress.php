<?php
/**
 * ResetCourseProgress.
 * php version 5.6
 *
 * @category ResetCourseProgress
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPressCourse\Actions;

use Exception;
use memberpress\courses\lib as lib;
use SureTriggers\Integrations\MemberPressCourse\MemberPressCourse;
use memberpress\courses as base;
use memberpress\courses\models as models;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * ResetCourseProgress
 *
 * @category ResetCourseProgress
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ResetCourseProgress extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MemberPressCourse';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mpc_reset_progress';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Reset Course Progress', 'suretriggers' ),
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
	 * @throws Exception Throws exception.
	 *
	 * @return array|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! $user_id ) {
			throw new Exception( 'User not found with this email address.' );
		}
		if ( ! class_exists( '\memberpress\courses\models\UserProgress' ) ) {
			return;
		}

		$course_id       = $selected_options['course'];
		$user_progresses = (array) models\UserProgress::find_all_by_user_and_course( $user_id, $course_id );
		if ( count( $user_progresses ) == 0 ) {
			throw new Exception( 'User has made no progress on the selected course.' );
		}

		foreach ( $user_progresses as $user_progress ) {
			$user_progress->destroy();
		}
		
		return [
			'user_email'                => $selected_options['wp_user_email'],
			'course_id'                 => $course_id,
			'course_title'              => get_the_title( $course_id ),
			'course_url'                => get_permalink( $course_id ),
			'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
			'course_featured_image_url' => get_the_post_thumbnail_url( $course_id ),
		];
	}


}

ResetCourseProgress::get_instance();
